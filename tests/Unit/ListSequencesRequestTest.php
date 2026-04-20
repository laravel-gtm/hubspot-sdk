<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\ListSequencesRequest;
use LaravelGtm\HubspotSdk\Responses\ListSequencesResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('resolves the sequences endpoint', function (): void {
    $request = new ListSequencesRequest(userId: '2222222');

    expect($request->resolveEndpoint())->toBe('/automation/sequences/2026-03');
});

it('builds query with userId, limit, and after', function (): void {
    $request = new ListSequencesRequest(
        userId: '2222222',
        limit: 25,
        after: 'cursor-abc',
    );

    $method = new ReflectionMethod(ListSequencesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([
        'userId' => '2222222',
        'limit' => 25,
        'after' => 'cursor-abc',
    ]);
});

it('omits null pagination params but always includes userId', function (): void {
    $request = new ListSequencesRequest(userId: '2222222');

    $method = new ReflectionMethod(ListSequencesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe(['userId' => '2222222']);
});

it('parses list response into ListSequencesResponse DTO', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        ListSequencesRequest::class => MockResponse::make([
            'total' => 2,
            'results' => [
                [
                    'id' => '111',
                    'name' => 'Outbound Warm Sequence',
                    'userId' => '2222222',
                    'folderId' => 'folder-1',
                    'createdAt' => '2026-01-15T10:00:00Z',
                    'updatedAt' => '2026-04-01T12:00:00Z',
                ],
                [
                    'id' => '222',
                    'name' => 'Re-engagement',
                    'userId' => '2222222',
                ],
            ],
            'paging' => ['next' => ['after' => 'cursor-next']],
        ], 200),
    ]));

    $response = $connector->send(new ListSequencesRequest(userId: '2222222'))->dtoOrFail();

    expect($response)->toBeInstanceOf(ListSequencesResponse::class);
    expect($response->total)->toBe(2);
    expect($response->results)->toHaveCount(2);
    expect($response->results[0]->id)->toBe('111');
    expect($response->results[0]->name)->toBe('Outbound Warm Sequence');
    expect($response->results[0]->folderId)->toBe('folder-1');
    expect($response->paging->hasNextPage())->toBeTrue();
    expect($response->paging->nextAfter)->toBe('cursor-next');
});
