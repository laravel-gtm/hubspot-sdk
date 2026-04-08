<?php

declare(strict_types=1);

use Saloon\Http\Auth\HeaderAuthenticator;
use YourVendor\SaloonApiSdk\SaloonConnector;

it('resolves custom base urls without trailing slash', function (): void {
    $connector = new SaloonConnector('https://example.test/', null);

    expect($connector->resolveBaseUrl())->toBe('https://example.test');
});

it('defaults to placeholder api host when no base url is set', function (): void {
    $connector = new SaloonConnector;

    expect($connector->resolveBaseUrl())->toBe('https://api.example.com');
});

it('returns null default auth when token is missing', function (): void {
    $connector = new SaloonConnector(null, null);
    $method = new ReflectionMethod(SaloonConnector::class, 'defaultAuth');

    expect($method->invoke($connector))->toBeNull();
});

it('builds header auth when token is provided', function (): void {
    $connector = new SaloonConnector(null, 'test-token');
    $method = new ReflectionMethod(SaloonConnector::class, 'defaultAuth');

    expect($method->invoke($connector))->toBeInstanceOf(HeaderAuthenticator::class);
});

it('uses custom auth header name', function (): void {
    $connector = new SaloonConnector(null, 'secret', 'Authorization');
    $method = new ReflectionMethod(SaloonConnector::class, 'defaultAuth');
    $auth = $method->invoke($connector);

    expect($auth)->toBeInstanceOf(HeaderAuthenticator::class);
});
