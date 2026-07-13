<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

it('invokes the onUnauthorized callback before the exception propagates', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $connector->withMockClient(new MockClient([
        GetContactRequest::class => MockResponse::make(['message' => 'expired token'], 401),
    ]));

    $received = null;
    $connector->onUnauthorized(function (Response $response) use (&$received): void {
        $received = $response;
    });

    expect(fn () => $connector->send(new GetContactRequest('12345')))
        ->toThrow(UnauthorizedException::class);

    expect($received)->toBeInstanceOf(Response::class);

    if (! $received instanceof Response) {
        throw new RuntimeException('Expected the onUnauthorized callback to receive a Response.');
    }

    expect($received->status())->toBe(401);
});

it('does not invoke the onUnauthorized callback on a successful response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $connector->withMockClient(new MockClient([
        GetContactRequest::class => MockResponse::make([
            'id' => '12345',
            'properties' => [],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-01-01T00:00:00.000Z',
            'archived' => false,
        ], 200),
    ]));

    $called = false;
    $connector->onUnauthorized(function () use (&$called): void {
        $called = true;
    });

    $connector->send(new GetContactRequest('12345'));

    expect($called)->toBeFalse();
});

it('is settable via the sdk and delegates to the connector', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $connector->withMockClient(new MockClient([
        GetContactRequest::class => MockResponse::make(['message' => 'expired token'], 401),
    ]));

    $sdk = new HubspotSdk($connector);
    $called = false;

    $result = $sdk->onUnauthorized(function () use (&$called): void {
        $called = true;
    });

    expect($result)->toBe($sdk);

    expect(fn () => $connector->send(new GetContactRequest('12345')))
        ->toThrow(UnauthorizedException::class);

    expect($called)->toBeTrue();
});
