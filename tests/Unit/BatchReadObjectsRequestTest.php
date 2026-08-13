<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\BatchReadObjectsRequest;
use LaravelGtm\HubspotSdk\Responses\BatchObjectsResponse;

it('resolves the batch read endpoint for any object type', function (): void {
    $request = new BatchReadObjectsRequest('contacts', ['1']);
    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/contacts/batch/read');
});

it('builds inputs and properties in the body', function (): void {
    $request = new BatchReadObjectsRequest('contacts', ['1', '2'], ['email', 'lifecyclestage']);

    $method = new ReflectionMethod(BatchReadObjectsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['inputs'])->toBe([['id' => '1'], ['id' => '2']]);
    expect($body['properties'])->toBe(['email', 'lifecyclestage']);
});

it('omits properties from the body when null', function (): void {
    $request = new BatchReadObjectsRequest('companies', ['5']);

    $method = new ReflectionMethod(BatchReadObjectsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toHaveKey('inputs');
    expect($body)->not->toHaveKey('properties');
});

it('puts the archived flag in the query string', function (): void {
    $request = new BatchReadObjectsRequest('contacts', ['1'], archived: true);

    $method = new ReflectionMethod(BatchReadObjectsRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['archived'])->toBe('true');
});

it('rejects more than 100 ids', function (): void {
    $ids = array_map(strval(...), range(1, 101));

    new BatchReadObjectsRequest('contacts', $ids);
})->throws(InvalidArgumentException::class, 'at most 100');

it('parses results and exposes result ids for deletion diffing', function (): void {
    $response = BatchObjectsResponse::fromArray([
        'status' => 'COMPLETE',
        'results' => [
            [
                'id' => '9001',
                'properties' => ['email' => 'jane@example.com', 'lifecyclestage' => '1342308877'],
                'createdAt' => '2026-08-01T00:00:00Z',
                'updatedAt' => '2026-08-12T00:00:00Z',
                'archived' => false,
            ],
        ],
        'numErrors' => 0,
    ]);

    expect($response->results)->toHaveCount(1);
    expect($response->results[0]->properties['lifecyclestage'])->toBe('1342308877');
    expect($response->resultIds())->toBe(['9001']);
    expect($response->numErrors)->toBe(0);
});
