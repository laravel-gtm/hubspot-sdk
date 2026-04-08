<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;

it('resolves the deals endpoint', function (): void {
    $request = new ListDealsRequest;

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/deals');
});

it('builds query parameters from constructor args', function (): void {
    $request = new ListDealsRequest(
        limit: 50,
        after: 'abc123',
        properties: ['dealname', 'amount'],
        archived: true,
    );
    $method = new ReflectionMethod(ListDealsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'limit' => 50,
        'after' => 'abc123',
        'properties' => 'dealname,amount',
        'archived' => 'true',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new ListDealsRequest;
    $method = new ReflectionMethod(ListDealsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});

it('joins associations as comma separated string', function (): void {
    $request = new ListDealsRequest(associations: ['contacts', 'companies']);
    $method = new ReflectionMethod(ListDealsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['associations'])->toBe('contacts,companies');
});
