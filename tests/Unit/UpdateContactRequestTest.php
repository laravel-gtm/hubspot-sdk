<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\UpdateContactRequest;
use Saloon\Enums\Method;

it('uses the PATCH method', function (): void {
    $request = new UpdateContactRequest('12345', ['lead_accepted' => 'accepted']);

    expect($request->getMethod())->toBe(Method::PATCH);
});

it('resolves the contact endpoint with id', function (): void {
    $request = new UpdateContactRequest('12345', ['lead_accepted' => 'accepted']);

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts/12345');
});

it('wraps properties in a properties envelope in the body', function (): void {
    $request = new UpdateContactRequest(
        contactId: '12345',
        properties: ['lead_accepted' => 'rejected', 'lifecyclestage' => 'other'],
    );
    $method = new ReflectionMethod(UpdateContactRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'properties' => [
            'lead_accepted' => 'rejected',
            'lifecyclestage' => 'other',
        ],
    ]);
});
