<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\CreateContactRequest;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

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
