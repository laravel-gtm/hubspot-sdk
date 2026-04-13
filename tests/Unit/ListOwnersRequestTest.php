<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListOwnersRequest;

it('resolves the owners endpoint', function (): void {
    $request = new ListOwnersRequest;
    expect($request->resolveEndpoint())->toBe('/crm/v3/owners');
});

it('builds query with email filter', function (): void {
    $request = new ListOwnersRequest(
        email: 'ethan@laravel.com',
        limit: 10,
    );

    $method = new ReflectionMethod(ListOwnersRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['email'])->toBe('ethan@laravel.com');
    expect($query['limit'])->toBe(10);
});

it('omits null query params', function (): void {
    $request = new ListOwnersRequest;

    $method = new ReflectionMethod(ListOwnersRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});
