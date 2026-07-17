<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\SetPrimaryCompanyAssociationRequest;
use LaravelGtm\HubspotSdk\Responses\AssociationResult;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('uses the PUT method', function (): void {
    $request = new SetPrimaryCompanyAssociationRequest('501', '20787072317');

    expect($request->getMethod())->toBe(Method::PUT);
});

it('resolves the primary company association endpoint with contact and company ids', function (): void {
    $request = new SetPrimaryCompanyAssociationRequest('501', '20787072317');

    expect($request->resolveEndpoint())->toBe(
        '/crm/v4/objects/contacts/501/associations/companies/20787072317',
    );
});

it('serializes the primary association type in the request body', function (): void {
    $request = new SetPrimaryCompanyAssociationRequest('501', '20787072317');

    $method = new ReflectionMethod(SetPrimaryCompanyAssociationRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        [
            'associationCategory' => 'HUBSPOT_DEFINED',
            'associationTypeId' => 1,
        ],
    ]);
});

it('parses the association result from the response', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        SetPrimaryCompanyAssociationRequest::class => MockResponse::make([
            'results' => [
                [
                    'fromObjectTypeId' => '0-1',
                    'fromObjectId' => '501',
                    'toObjectTypeId' => '0-2',
                    'toObjectId' => '20787072317',
                    'labels' => ['Primary'],
                ],
            ],
        ], 200),
    ]));

    $response = $connector->send(new SetPrimaryCompanyAssociationRequest('501', '20787072317'))->dtoOrFail();

    expect($response)->toBeInstanceOf(AssociationResult::class);
    expect($response->fromObjectId)->toBe('501');
    expect($response->toObjectId)->toBe('20787072317');
    expect($response->labels)->toBe(['Primary']);
});

it('propagates a not found error when the contact or company does not exist', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        SetPrimaryCompanyAssociationRequest::class => MockResponse::make(['message' => 'contact not found'], 404),
    ]));

    expect(fn () => $connector->send(new SetPrimaryCompanyAssociationRequest('501', '20787072317'))->dtoOrFail())
        ->toThrow(NotFoundException::class);
});
