<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Responses\Deal;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use LaravelGtm\HubspotSdk\Responses\Paging;

it('constructs a deal from array with all fields', function (): void {
    $deal = Deal::fromArray([
        'id' => '99',
        'properties' => ['dealname' => 'Big Deal', 'amount' => '50000'],
        'createdAt' => '2023-01-01T00:00:00.000Z',
        'updatedAt' => '2023-06-01T00:00:00.000Z',
        'archived' => false,
        'archivedAt' => null,
    ]);

    expect($deal->id)->toBe('99');
    expect($deal->properties)->toBe(['dealname' => 'Big Deal', 'amount' => '50000']);
    expect($deal->archived)->toBeFalse();
    expect($deal->archivedAt)->toBeNull();
});

it('handles missing optional deal fields gracefully', function (): void {
    $deal = Deal::fromArray([
        'id' => '1',
        'properties' => [],
        'createdAt' => '2023-01-01T00:00:00.000Z',
        'updatedAt' => '2023-01-01T00:00:00.000Z',
    ]);

    expect($deal->archived)->toBeFalse();
    expect($deal->archivedAt)->toBeNull();
});

it('constructs paging from array with next cursor', function (): void {
    $paging = Paging::fromArray([
        'next' => [
            'after' => 'abc123',
            'link' => 'https://api.hubapi.com/crm/v3/objects/deals?after=abc123',
        ],
    ]);

    expect($paging->nextAfter)->toBe('abc123');
    expect($paging->hasNextPage())->toBeTrue();
});

it('constructs paging from null as no next page', function (): void {
    $paging = Paging::fromArray(null);

    expect($paging->nextAfter)->toBeNull();
    expect($paging->nextLink)->toBeNull();
    expect($paging->hasNextPage())->toBeFalse();
});

it('constructs list deals response from array', function (): void {
    $response = ListDealsResponse::fromArray([
        'results' => [
            [
                'id' => '1',
                'properties' => ['dealname' => 'Deal A'],
                'createdAt' => '2023-01-01T00:00:00.000Z',
                'updatedAt' => '2023-01-01T00:00:00.000Z',
                'archived' => false,
            ],
            [
                'id' => '2',
                'properties' => ['dealname' => 'Deal B'],
                'createdAt' => '2023-02-01T00:00:00.000Z',
                'updatedAt' => '2023-02-01T00:00:00.000Z',
                'archived' => true,
                'archivedAt' => '2023-03-01T00:00:00.000Z',
            ],
        ],
        'paging' => [
            'next' => ['after' => 'next-cursor'],
        ],
    ]);

    expect($response->results)->toHaveCount(2);
    expect($response->results[0]->id)->toBe('1');
    expect($response->results[1]->archived)->toBeTrue();
    expect($response->results[1]->archivedAt)->toBe('2023-03-01T00:00:00.000Z');
    expect($response->paging->nextAfter)->toBe('next-cursor');
});
