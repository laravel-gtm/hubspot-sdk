<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListContactsRequest;

it('resolves the contacts endpoint', function (): void {
    $request = new ListContactsRequest;

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts');
});

it('builds query parameters from constructor args', function (): void {
    $request = new ListContactsRequest(
        limit: 50,
        after: 'abc123',
        properties: ['email', 'firstname'],
        archived: true,
    );
    $method = new ReflectionMethod(ListContactsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'limit' => 50,
        'after' => 'abc123',
        'properties' => 'email,firstname',
        'archived' => 'true',
    ]);
});

it('omits null parameters from query', function (): void {
    $request = new ListContactsRequest;
    $method = new ReflectionMethod(ListContactsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});

it('joins associations as comma separated string', function (): void {
    $request = new ListContactsRequest(associations: ['deals', 'companies']);
    $method = new ReflectionMethod(ListContactsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['associations'])->toBe('deals,companies');
});
