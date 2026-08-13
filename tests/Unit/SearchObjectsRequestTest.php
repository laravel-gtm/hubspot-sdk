<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\SearchObjectsRequest;
use LaravelGtm\HubspotSdk\Responses\CrmObject;
use LaravelGtm\HubspotSdk\Responses\SearchObjectsResponse;

it('resolves the search endpoint for a custom object type id', function (): void {
    $request = new SearchObjectsRequest('2-61391055');
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/2-61391055/search');
});

it('resolves the search endpoint for a named object type', function (): void {
    $request = new SearchObjectsRequest('contacts');
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts/search');
});

it('builds body with filter groups, properties, and sorts', function (): void {
    $request = new SearchObjectsRequest(
        objectTypeId: '2-61391055',
        filterGroups: [
            [
                'filters' => [
                    ['propertyName' => 'hs_object_id', 'operator' => 'GT', 'value' => '500'],
                ],
            ],
        ],
        properties: ['mql_reason', 'mql_date'],
        limit: 200,
        after: '200',
        sorts: [['propertyName' => 'hs_object_id', 'direction' => 'ASCENDING']],
    );

    $method = new ReflectionMethod(SearchObjectsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['filterGroups'])->toHaveCount(1);
    expect($body['properties'])->toContain('mql_reason', 'mql_date');
    expect($body['limit'])->toBe(200);
    expect($body['after'])->toBe('200');
    expect($body['sorts'])->toHaveCount(1);
});

it('omits null fields from body', function (): void {
    $request = new SearchObjectsRequest('2-63308387');

    $method = new ReflectionMethod(SearchObjectsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toHaveKey('filterGroups');
    expect($body)->not->toHaveKey('properties');
    expect($body)->not->toHaveKey('limit');
    expect($body)->not->toHaveKey('after');
    expect($body)->not->toHaveKey('sorts');
});

it('parses results into generic crm objects', function (): void {
    $response = SearchObjectsResponse::fromArray([
        'total' => 2,
        'results' => [
            [
                'id' => '101',
                'properties' => ['mql_reason' => 'Contact Sales Request', 'mql_date' => '2026-08-11T19:28:00Z'],
                'createdAt' => '2026-08-11T19:28:01Z',
                'updatedAt' => '2026-08-11T19:28:01Z',
                'archived' => false,
            ],
            [
                'id' => '102',
                'properties' => ['mql_reason' => null],
                'createdAt' => '2026-08-12T10:00:00Z',
                'updatedAt' => '2026-08-12T10:00:00Z',
                'archived' => false,
            ],
        ],
        'paging' => ['next' => ['after' => '102']],
    ]);

    expect($response->total)->toBe(2);
    expect($response->results)->toHaveCount(2);
    expect($response->results[0])->toBeInstanceOf(CrmObject::class);
    expect($response->results[0]->properties['mql_reason'])->toBe('Contact Sales Request');
    expect($response->paging->hasNextPage())->toBeTrue();
    expect($response->paging->nextAfter)->toBe('102');
});
