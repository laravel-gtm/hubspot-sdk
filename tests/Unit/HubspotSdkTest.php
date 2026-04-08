<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;
use LaravelGtm\HubspotSdk\Responses\Deal;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

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
