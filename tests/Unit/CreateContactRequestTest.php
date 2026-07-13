<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\CreateContactRequest;
use Saloon\Enums\Method;

it('uses the POST method', function (): void {
    $request = new CreateContactRequest(['email' => 'jane@example.com']);

    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves the contacts collection endpoint', function (): void {
    $request = new CreateContactRequest(['email' => 'jane@example.com']);

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts');
});

it('wraps properties in a properties envelope in the body', function (): void {
    $request = new CreateContactRequest(['email' => 'jane@example.com', 'firstname' => 'Jane']);
    $method = new ReflectionMethod(CreateContactRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'properties' => [
            'email' => 'jane@example.com',
            'firstname' => 'Jane',
        ],
    ]);
});
