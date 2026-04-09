<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListDealPropertiesRequest;

it('resolves the deal properties endpoint', function (): void {
    $request = new ListDealPropertiesRequest;

    expect($request->resolveEndpoint())->toBe('/crm/properties/2026-03/deals');
});

it('builds query parameters from constructor args', function (): void {
    $request = new ListDealPropertiesRequest(
        archived: true,
        dataSensitivity: 'sensitive',
        locale: 'en-us',
        properties: 'dealname,amount',
    );
    $method = new ReflectionMethod(ListDealPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'archived' => 'true',
        'dataSensitivity' => 'sensitive',
        'locale' => 'en-us',
        'properties' => 'dealname,amount',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new ListDealPropertiesRequest;
    $method = new ReflectionMethod(ListDealPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});

it('serializes archived false', function (): void {
    $request = new ListDealPropertiesRequest(archived: false);
    $method = new ReflectionMethod(ListDealPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['archived'])->toBe('false');
});
