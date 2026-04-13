<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\SearchContactsRequest;

it('resolves the contacts search endpoint', function (): void {
    $request = new SearchContactsRequest;
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts/search');
});

it('builds body with filter groups and properties', function (): void {
    $request = new SearchContactsRequest(
        filterGroups: [
            [
                'filters' => [
                    ['propertyName' => 'email', 'operator' => 'HAS_PROPERTY'],
                ],
            ],
        ],
        properties: ['firstname', 'lastname', 'email'],
        limit: 50,
        after: 'abc123',
        sorts: [['propertyName' => 'lastname', 'direction' => 'ASCENDING']],
    );

    $method = new ReflectionMethod(SearchContactsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['filterGroups'])->toHaveCount(1);
    expect($body['properties'])->toContain('email', 'firstname');
    expect($body['limit'])->toBe(50);
    expect($body['after'])->toBe('abc123');
    expect($body['sorts'])->toHaveCount(1);
});

it('omits null fields from body', function (): void {
    $request = new SearchContactsRequest(
        filterGroups: [['filters' => []]],
    );

    $method = new ReflectionMethod(SearchContactsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toHaveKey('filterGroups');
    expect($body)->not->toHaveKey('properties');
    expect($body)->not->toHaveKey('limit');
    expect($body)->not->toHaveKey('after');
});
