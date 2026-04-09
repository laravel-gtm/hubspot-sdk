<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\GetContactRequest;

it('resolves the contact endpoint with id', function (): void {
    $request = new GetContactRequest('12345');

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts/12345');
});

it('builds query parameters from constructor args', function (): void {
    $request = new GetContactRequest(
        contactId: '12345',
        properties: ['email', 'firstname'],
        associations: ['deals', 'companies'],
        archived: false,
    );
    $method = new ReflectionMethod(GetContactRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'properties' => 'email,firstname',
        'associations' => 'deals,companies',
        'archived' => 'false',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new GetContactRequest('12345');
    $method = new ReflectionMethod(GetContactRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});
