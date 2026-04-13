<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\GetCompanyRequest;

it('resolves the company endpoint with id', function (): void {
    $request = new GetCompanyRequest('12345');

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/companies/12345');
});

it('builds query parameters from constructor args', function (): void {
    $request = new GetCompanyRequest(
        companyId: '12345',
        properties: ['name', 'domain'],
        associations: ['contacts', 'deals'],
        archived: false,
    );
    $method = new ReflectionMethod(GetCompanyRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'properties' => 'name,domain',
        'associations' => 'contacts,deals',
        'archived' => 'false',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new GetCompanyRequest('12345');
    $method = new ReflectionMethod(GetCompanyRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});
