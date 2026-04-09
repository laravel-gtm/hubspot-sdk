<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListContactPropertiesRequest;

it('resolves the contact properties endpoint', function (): void {
    $request = new ListContactPropertiesRequest;

    expect($request->resolveEndpoint())->toBe('/crm/v3/properties/contact');
});

it('builds query parameters from constructor args', function (): void {
    $request = new ListContactPropertiesRequest(
        archived: true,
        dataSensitivity: 'sensitive',
        locale: 'en-us',
        properties: 'email,firstname',
    );
    $method = new ReflectionMethod(ListContactPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'archived' => 'true',
        'dataSensitivity' => 'sensitive',
        'locale' => 'en-us',
        'properties' => 'email,firstname',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new ListContactPropertiesRequest;
    $method = new ReflectionMethod(ListContactPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});

it('serializes archived false', function (): void {
    $request = new ListContactPropertiesRequest(archived: false);
    $method = new ReflectionMethod(ListContactPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['archived'])->toBe('false');
});
