<?php

declare(strict_types=1);

namespace YourVendor\SaloonApiSdk;

use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Http\Connector;
use Saloon\Http\Response;
use Saloon\RateLimitPlugin\Contracts\RateLimitStore;
use Saloon\RateLimitPlugin\Limit;
use Saloon\RateLimitPlugin\Stores\MemoryStore;
use Saloon\RateLimitPlugin\Traits\HasRateLimits;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;
use Saloon\Traits\Plugins\HasTimeout;

class SaloonConnector extends Connector
{
    use AlwaysThrowOnErrors;
    use HasRateLimits;
    use HasTimeout;

    protected int $connectTimeout = 10;

    protected int $requestTimeout = 30;

    private readonly ?RateLimitStore $customRateLimitStore;

    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $token = null,
        private readonly string $authHeaderName = 'X-Api-Key',
        ?RateLimitStore $rateLimitStore = null,
    ) {
        $this->customRateLimitStore = $rateLimitStore;
    }

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl ?? 'https://api.example.com', '/');
    }

    protected function defaultAuth(): ?HeaderAuthenticator
    {
        if ($this->token === null || $this->token === '') {
            return null;
        }

        return new HeaderAuthenticator($this->token, $this->authHeaderName);
    }

    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * @return array<int, Limit>
     */
    protected function resolveLimits(): array
    {
        return [
            Limit::allow(60)->everyMinute()->name('default'),
        ];
    }

    protected function resolveRateLimitStore(): RateLimitStore
    {
        return $this->customRateLimitStore ?? new MemoryStore;
    }

    protected function handleTooManyAttempts(Response $response, Limit $limit): void
    {
        if ($response->status() !== 429) {
            return;
        }

        $limit->exceeded(releaseInSeconds: 60);
    }
}
