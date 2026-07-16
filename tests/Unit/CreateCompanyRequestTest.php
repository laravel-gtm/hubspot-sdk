<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\CreateCompanyRequest;
use Saloon\Enums\Method;

it('uses the POST method', function (): void {
    $request = new CreateCompanyRequest(['name' => 'Acme Corp']);

    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves the companies collection endpoint', function (): void {
    $request = new CreateCompanyRequest(['name' => 'Acme Corp']);

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/companies');
});

it('wraps properties in a properties envelope in the body', function (): void {
    $request = new CreateCompanyRequest(['name' => 'Acme Corp', 'domain' => 'acme.example']);
    $method = new ReflectionMethod(CreateCompanyRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'properties' => [
            'name' => 'Acme Corp',
            'domain' => 'acme.example',
        ],
    ]);
});

it('never retries, since a create is not idempotent', function (): void {
    $request = new CreateCompanyRequest(['name' => 'Acme Corp']);

    expect($request->tries)->toBe(1);
});
