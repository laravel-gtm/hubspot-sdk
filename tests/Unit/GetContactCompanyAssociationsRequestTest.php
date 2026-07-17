<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\GetContactCompanyAssociationsRequest;
use LaravelGtm\HubspotSdk\Responses\ContactCompanyAssociationsResponse;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('uses the GET method', function (): void {
    $request = new GetContactCompanyAssociationsRequest('501');

    expect($request->getMethod())->toBe(Method::GET);
});

it('resolves the contact company associations endpoint', function (): void {
    $request = new GetContactCompanyAssociationsRequest('501');

    expect($request->resolveEndpoint())->toBe(
        '/crm/v4/objects/contacts/501/associations/companies',
    );
});

it('parses results with association types into the response DTO', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        GetContactCompanyAssociationsRequest::class => MockResponse::make([
            'results' => [
                [
                    'toObjectId' => '20787072317',
                    'associationTypes' => [
                        ['category' => 'HUBSPOT_DEFINED', 'typeId' => 1, 'label' => 'Primary'],
                        ['category' => 'HUBSPOT_DEFINED', 'typeId' => 279, 'label' => null],
                    ],
                ],
                [
                    'toObjectId' => '999',
                    'associationTypes' => [
                        ['category' => 'HUBSPOT_DEFINED', 'typeId' => 279, 'label' => null],
                    ],
                ],
            ],
            'paging' => [
                'next' => ['after' => 'cursor123'],
            ],
        ], 200),
    ]));

    $response = $connector->send(new GetContactCompanyAssociationsRequest('501'))->dtoOrFail();

    expect($response)->toBeInstanceOf(ContactCompanyAssociationsResponse::class);
    expect($response->results)->toHaveCount(2);

    $primary = $response->results[0];
    expect($primary->companyId)->toBe('20787072317');
    expect($primary->isPrimary())->toBeTrue();
    expect($primary->associationTypes[0]->category)->toBe('HUBSPOT_DEFINED');
    expect($primary->associationTypes[0]->typeId)->toBe(1);
    expect($primary->associationTypes[0]->label)->toBe('Primary');

    $nonPrimary = $response->results[1];
    expect($nonPrimary->companyId)->toBe('999');
    expect($nonPrimary->isPrimary())->toBeFalse();

    expect($response->paging->nextAfter)->toBe('cursor123');
});

it('propagates a not found error when the contact does not exist', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        GetContactCompanyAssociationsRequest::class => MockResponse::make(['message' => 'contact not found'], 404),
    ]));

    expect(fn () => $connector->send(new GetContactCompanyAssociationsRequest('501'))->dtoOrFail())
        ->toThrow(NotFoundException::class);
});
