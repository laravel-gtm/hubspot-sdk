<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\SearchCompaniesRequest;

it('resolves the companies search endpoint', function (): void {
    $request = new SearchCompaniesRequest;
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/companies/search');
});

it('builds body with filter groups and properties', function (): void {
    $request = new SearchCompaniesRequest(
        filterGroups: [
            [
                'filters' => [
                    ['propertyName' => 'hubspot_owner_id', 'operator' => 'HAS_PROPERTY'],
                ],
            ],
        ],
        properties: ['name', 'domain', 'industry', 'hubspot_owner_id'],
        limit: 100,
        after: 'abc123',
        sorts: [['propertyName' => 'name', 'direction' => 'ASCENDING']],
    );

    $method = new ReflectionMethod(SearchCompaniesRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['filterGroups'])->toHaveCount(1);
    expect($body['properties'])->toContain('name', 'hubspot_owner_id');
    expect($body['limit'])->toBe(100);
    expect($body['after'])->toBe('abc123');
    expect($body['sorts'])->toHaveCount(1);
});

it('omits null fields from body', function (): void {
    $request = new SearchCompaniesRequest(
        filterGroups: [['filters' => []]],
    );

    $method = new ReflectionMethod(SearchCompaniesRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toHaveKey('filterGroups');
    expect($body)->not->toHaveKey('properties');
    expect($body)->not->toHaveKey('limit');
    expect($body)->not->toHaveKey('after');
});
