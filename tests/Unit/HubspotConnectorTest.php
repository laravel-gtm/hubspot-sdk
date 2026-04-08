<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use Saloon\Http\Auth\TokenAuthenticator;

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
