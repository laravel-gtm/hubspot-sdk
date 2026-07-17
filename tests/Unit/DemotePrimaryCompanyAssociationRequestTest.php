<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\DemotePrimaryCompanyAssociationRequest;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('uses the POST method', function (): void {
    $request = new DemotePrimaryCompanyAssociationRequest('501', '20787072317');

    expect($request->getMethod())->toBe(Method::POST);
});

it('resolves the batch labels archive endpoint', function (): void {
    $request = new DemotePrimaryCompanyAssociationRequest('501', '20787072317');

    expect($request->resolveEndpoint())->toBe(
        '/crm/v4/associations/contacts/companies/batch/labels/archive',
    );
});

it('serializes the archive-primary-label body', function (): void {
    $request = new DemotePrimaryCompanyAssociationRequest('501', '20787072317');

    $method = new ReflectionMethod(DemotePrimaryCompanyAssociationRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'inputs' => [
            [
                'from' => ['id' => '501'],
                'to' => ['id' => '20787072317'],
                'types' => [
                    [
                        'associationCategory' => 'HUBSPOT_DEFINED',
                        'associationTypeId' => 1,
                    ],
                ],
            ],
        ],
    ]);
});

it('sends successfully on a 204 No Content response', function (): void {
    $connector = new HubspotConnector;
    $mockClient = new MockClient([
        DemotePrimaryCompanyAssociationRequest::class => MockResponse::make([], 204),
    ]);
    $connector->withMockClient($mockClient);

    $connector->send(new DemotePrimaryCompanyAssociationRequest('501', '20787072317'));

    $mockClient->assertSent(DemotePrimaryCompanyAssociationRequest::class);
});

it('propagates a not found error when the contact or company does not exist', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        DemotePrimaryCompanyAssociationRequest::class => MockResponse::make(['message' => 'not found'], 404),
    ]));

    expect(fn () => $connector->send(new DemotePrimaryCompanyAssociationRequest('501', '20787072317')))
        ->toThrow(NotFoundException::class);
});
