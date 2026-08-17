<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk;

use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Saloon\Traits\Plugins\HasTimeout;

class HubspotConnector extends Connector
{
    use AlwaysThrowOnErrors;
    use HasRateLimits;
    use HasTimeout;

    public ?int $tries = 3;

    public ?int $retryInterval = 500;

    public ?bool $useExponentialBackoff = true;

    protected int $connectTimeout = 10;

    protected int $requestTimeout = 30;

    private readonly ?RateLimitStore $customRateLimitStore;

    /**
     * @var (\Closure(Response): void)|null
     */
    private ?\Closure $onUnauthorized = null;

    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $token = null,
        ?RateLimitStore $rateLimitStore = null,
        private readonly int $burstLimit = 150,
        private readonly int $dailyLimit = 1000000,
    ) {
        $this->customRateLimitStore = $rateLimitStore;
    }

    /**
     * Register a callback invoked whenever a request fails with a 401 Unauthorized
     * response, before the exception propagates. Transport-layer only: it observes
     * the response but does not suppress or alter the resulting exception.
     *
     * @param  callable(Response): void  $callback
     */
    public function onUnauthorized(callable $callback): static
    {
        $this->onUnauthorized = $callback(...);

        return $this;
    }

    public function boot(PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(function (Response $response): void {
            if ($this->onUnauthorized !== null && $response->status() === 401) {
                ($this->onUnauthorized)($response);
            }
        });
    }

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl ?? 'https://api.hubapi.com', '/');
    }

    protected function defaultAuth(): ?TokenAuthenticator
    {
        if ($this->token === null || $this->token === '') {
            return null;
        }

        return new TokenAuthenticator($this->token);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * The burst limit sleeps: this connector is typically bound as a
     * container singleton over a shared (cache-backed) rate limit store, so
     * one caller's burst can trip the limit for every other concurrent
     * caller. Without sleep(), Saloon throws RateLimitReachedException
     * immediately instead of retrying, which turns a brief, shared 10-second
     * throttle into a hard failure for whichever request happens to be
     * unlucky. The daily limit deliberately does NOT sleep — its window is a
     * full day, and blocking a queue worker for up to 24 hours is worse than
     * failing fast.
     *
     * @return array<int, Limit>
     */
    protected function resolveLimits(): array
    {
        return [
            Limit::allow($this->burstLimit)->everySeconds(10)->name('burst')->sleep(),
            Limit::allow($this->dailyLimit)->everyDay()->name('daily'),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return $this->customRateLimitStore ?? new MemoryStore;
    }

    /**
     * Sleep (delay + retry) instead of throwing when HubSpot itself returns a
     * 429, for the same shared-store reason as the burst limit above.
     */
    protected function getTooManyAttemptsLimiter(): ?Limit
    {
        return Limit::custom($this->handleTooManyAttempts(...))->sleep();
    }

    protected function handleTooManyAttempts(Response $response, Limit $limit): void
    {
        if ($response->status() !== 429) {
            return;
        }

        $retryAfter = $response->header('Retry-After');

        $releaseSeconds = $retryAfter !== null && $retryAfter !== ''
            ? (int) $retryAfter
            : 10;

        $limit->exceeded(releaseInSeconds: $releaseSeconds);
    }
}
