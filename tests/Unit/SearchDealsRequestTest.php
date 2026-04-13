<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\SearchDealsRequest;

it('resolves the deals search endpoint', function (): void {
    $request = new SearchDealsRequest;
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/deals/search');
});

it('builds body with filter groups and properties', function (): void {
    $request = new SearchDealsRequest(
        filterGroups: [
            [
                'filters' => [
                    ['propertyName' => 'dealstage', 'operator' => 'EQ', 'value' => 'closedwon'],
                ],
            ],
        ],
        properties: ['dealname', 'amount', 'dealstage'],
        limit: 20,
        after: 'abc123',
        sorts: [['propertyName' => 'hs_lastmodifieddate', 'direction' => 'DESCENDING']],
    );

    $method = new ReflectionMethod(SearchDealsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['filterGroups'])->toHaveCount(1);
    expect($body['properties'])->toContain('dealname', 'amount');
    expect($body['limit'])->toBe(20);
    expect($body['after'])->toBe('abc123');
    expect($body['sorts'])->toHaveCount(1);
});

it('omits null fields from body', function (): void {
    $request = new SearchDealsRequest(
        filterGroups: [['filters' => []]],
    );

    $method = new ReflectionMethod(SearchDealsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toHaveKey('filterGroups');
    expect($body)->not->toHaveKey('properties');
    expect($body)->not->toHaveKey('limit');
    expect($body)->not->toHaveKey('after');
});
