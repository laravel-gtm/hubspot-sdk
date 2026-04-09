<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\GetDealRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;
use LaravelGtm\HubspotSdk\Responses\Association;
use LaravelGtm\HubspotSdk\Responses\CrmProperty;
use LaravelGtm\HubspotSdk\Responses\Deal;
use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns a single deal with associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetDealRequest::class => MockResponse::make([
            'id' => '57030476464',
            'properties' => ['dealname' => 'Big Deal', 'amount' => '50000'],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
            'archived' => false,
            'associations' => [
                'contacts' => [
                    'results' => [
                        ['id' => '101', 'type' => 'deal_to_contact'],
                        ['id' => '102', 'type' => 'deal_to_contact'],
                    ],
                ],
                'companies' => [
                    'results' => [
                        ['id' => '201', 'type' => 'deal_to_company'],
                    ],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getDeal('57030476464', associations: ['contacts', 'companies']);

    expect($response)->toBeInstanceOf(GetDealResponse::class);
    expect($response->id)->toBe('57030476464');
    expect($response->properties['dealname'])->toBe('Big Deal');
    expect($response->associations)->toHaveKeys(['contacts', 'companies']);
    expect($response->associations['contacts'])->toHaveCount(2);
    expect($response->associations['contacts'][0])->toBeInstanceOf(Association::class);
    expect($response->associations['contacts'][0]->id)->toBe('101');
    expect($response->associations['companies'])->toHaveCount(1);

    $mockClient->assertSent(GetDealRequest::class);
});

it('returns a single deal without associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetDealRequest::class => MockResponse::make([
            'id' => '123',
            'properties' => ['dealname' => 'Simple Deal'],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
            'archived' => false,
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getDeal('123');

    expect($response)->toBeInstanceOf(GetDealResponse::class);
    expect($response->id)->toBe('123');
    expect($response->associations)->toBeEmpty();

    $mockClient->assertSent(GetDealRequest::class);
});

it('returns a list deals response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealsRequest::class => MockResponse::make([
            'results' => [
                [
                    'id' => '123',
                    'properties' => ['dealname' => 'Test Deal', 'amount' => '1000'],
                    'createdAt' => '2023-01-01T00:00:00.000Z',
                    'updatedAt' => '2023-06-15T12:00:00.000Z',
                    'archived' => false,
                ],
            ],
            'paging' => [
                'next' => [
                    'after' => '456',
                    'link' => 'https://api.hubapi.com/crm/v3/objects/deals?after=456',
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDeals(limit: 10);

    expect($response)->toBeInstanceOf(ListDealsResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(Deal::class);
    expect($response->results[0]->id)->toBe('123');
    expect($response->results[0]->properties['dealname'])->toBe('Test Deal');
    expect($response->paging->nextAfter)->toBe('456');
    expect($response->paging->hasNextPage())->toBeTrue();

    $mockClient->assertSent(ListDealsRequest::class);
});

it('returns deals with no next page', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealsRequest::class => MockResponse::make([
            'results' => [],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDeals();

    expect($response->results)->toBeEmpty();
    expect($response->paging->hasNextPage())->toBeFalse();
});

it('returns a list deal properties response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealPropertiesRequest::class => MockResponse::make([
            'results' => [
                [
                    'name' => 'dealname',
                    'label' => 'Deal Name',
                    'type' => 'string',
                    'fieldType' => 'text',
                    'groupName' => 'dealinformation',
                    'description' => 'The name of the deal',
                    'options' => [],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDealProperties(archived: false, dataSensitivity: 'non_sensitive');

    expect($response)->toBeInstanceOf(ListDealPropertiesResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(CrmProperty::class);
    expect($response->results[0]->name)->toBe('dealname');
    expect($response->results[0]->label)->toBe('Deal Name');

    $mockClient->assertSent(ListDealPropertiesRequest::class);
});
