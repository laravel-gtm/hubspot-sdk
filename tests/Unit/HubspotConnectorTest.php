<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\CreateContactRequest;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\RateLimitPlugin\Limit;

it('resolves custom base urls without trailing slash', function (): void {
    $connector = new HubspotConnector('https://example.test/', null);

    expect($connector->resolveBaseUrl())->toBe('https://example.test');
});

it('defaults to hubspot api base url when no base url is set', function (): void {
    $connector = new HubspotConnector;

    expect($connector->resolveBaseUrl())->toBe('https://api.hubapi.com');
});

it('returns null default auth when token is missing', function (): void {
    $connector = new HubspotConnector(null, null);
    $method = new ReflectionMethod(HubspotConnector::class, 'defaultAuth');

    expect($method->invoke($connector))->toBeNull();
});

it('builds bearer token auth when token is provided', function (): void {
    $connector = new HubspotConnector(null, 'test-token');
    $method = new ReflectionMethod(HubspotConnector::class, 'defaultAuth');

    expect($method->invoke($connector))->toBeInstanceOf(TokenAuthenticator::class);
});

it('uses configurable burst rate limit', function (): void {
    $connector = new HubspotConnector(null, null, null, burstLimit: 100);
    $method = new ReflectionMethod(HubspotConnector::class, 'resolveLimits');
    $limits = $method->invoke($connector);

    expect($limits)->toHaveCount(2);
});

it('does not retry a create request after a failure, since creates are not idempotent', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        CreateContactRequest::class => MockResponse::make(['message' => 'server error'], 500),
    ]);
    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new CreateContactRequest(['email' => 'jane@example.com'])))
        ->toThrow(ServerException::class);

    $mockClient->assertSentCount(1);
});

it('retries an idempotent get request up to the configured number of tries', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $connector->retryInterval = 0;

    $mockClient = new MockClient([
        GetContactRequest::class => MockResponse::make(['message' => 'server error'], 500),
    ]);
    $connector->withMockClient($mockClient);

    expect(fn () => $connector->send(new GetContactRequest('501')))
        ->toThrow(ServerException::class);

    $mockClient->assertSentCount(3);
});

/**
 * @return array<int, Limit>
 */
function connectorLimits(HubspotConnector $connector): array
{
    $method = new ReflectionMethod(HubspotConnector::class, 'resolveLimits');
    $limits = $method->invoke($connector);

    if (! is_array($limits)) {
        throw new RuntimeException('Expected HubspotConnector::resolveLimits() to return an array.');
    }

    return array_values(array_filter($limits, fn (mixed $limit): bool => $limit instanceof Limit));
}

/**
 * @param  array<int, Limit>  $limits
 */
function findLimitByName(array $limits, string $needle): Limit
{
    foreach ($limits as $limit) {
        if (str_contains($limit->getName(), $needle)) {
            return $limit;
        }
    }

    throw new RuntimeException("No limit found matching \"{$needle}\".");
}

it('sleeps instead of throwing when the burst limit is exceeded', function (): void {
    $connector = new HubspotConnector(null, null);
    $burst = findLimitByName(connectorLimits($connector), 'burst');

    expect($burst->getShouldSleep())->toBeTrue();
});

it('does not sleep on the daily limit, to avoid blocking a queue worker for up to 24 hours', function (): void {
    $connector = new HubspotConnector(null, null);
    $daily = findLimitByName(connectorLimits($connector), 'daily');

    expect($daily->getShouldSleep())->toBeFalse();
});

it('sleeps instead of throwing when the auto-detected too-many-attempts limiter is exceeded', function (): void {
    $connector = new HubspotConnector(null, null);
    $method = new ReflectionMethod(HubspotConnector::class, 'getTooManyAttemptsLimiter');
    $limit = $method->invoke($connector);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->getShouldSleep())->toBeTrue();
});

it('delays and retries a request that receives a 429, instead of throwing RateLimitReachedException', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');

    $attempt = 0;
    $mockClient = new MockClient([
        GetContactRequest::class => function () use (&$attempt): MockResponse {
            $attempt++;

            return $attempt === 1
                ? MockResponse::make(['message' => 'rate limited'], 429, ['Retry-After' => '1'])
                : MockResponse::make([
                    'id' => '501',
                    'properties' => [],
                    'createdAt' => '2024-01-01T00:00:00Z',
                    'updatedAt' => '2024-01-01T00:00:00Z',
                    'archived' => false,
                ]);
        },
    ]);
    $connector->withMockClient($mockClient);

    $response = $connector->send(new GetContactRequest('501'));

    expect($response->status())->toBe(200)
        ->and($attempt)->toBe(2);
})->group('slow');
