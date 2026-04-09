<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\GetDealRequest;

it('resolves the deal endpoint with id', function (): void {
    $request = new GetDealRequest('12345');

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/deals/12345');
});

it('builds query parameters from constructor args', function (): void {
    $request = new GetDealRequest(
        dealId: '12345',
        properties: ['dealname', 'amount'],
        associations: ['contacts', 'companies'],
        archived: false,
    );
    $method = new ReflectionMethod(GetDealRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'properties' => 'dealname,amount',
        'associations' => 'contacts,companies',
        'archived' => 'false',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new GetDealRequest('12345');
    $method = new ReflectionMethod(GetDealRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});
