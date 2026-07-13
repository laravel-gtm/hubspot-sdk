<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\UpdateCompanyRequest;
use Saloon\Enums\Method;

it('uses the PATCH method', function (): void {
    $request = new UpdateCompanyRequest('20787072317', ['name' => 'Acme Corp']);

    expect($request->getMethod())->toBe(Method::PATCH);
});

it('resolves the company endpoint with id', function (): void {
    $request = new UpdateCompanyRequest('20787072317', ['name' => 'Acme Corp']);

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/companies/20787072317');
});

it('wraps properties in a properties envelope in the body', function (): void {
    $request = new UpdateCompanyRequest(
        companyId: '20787072317',
        properties: ['name' => 'Acme Corporation', 'domain' => 'acme.example'],
    );
    $method = new ReflectionMethod(UpdateCompanyRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'properties' => [
            'name' => 'Acme Corporation',
            'domain' => 'acme.example',
        ],
    ]);
});
