<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\GetCompanyContactAssociationsRequest;

it('resolves the company contact associations endpoint', function (): void {
    $request = new GetCompanyContactAssociationsRequest('12345');
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/companies/12345/associations/contacts');
});

it('builds query parameters from constructor args', function (): void {
    $request = new GetCompanyContactAssociationsRequest(
        companyId: '12345',
        limit: 50,
        after: 'cursor123',
    );

    $method = new ReflectionMethod(GetCompanyContactAssociationsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'limit' => 50,
        'after' => 'cursor123',
    ]);
});

it('omits null query params', function (): void {
    $request = new GetCompanyContactAssociationsRequest('12345');

    $method = new ReflectionMethod(GetCompanyContactAssociationsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});
