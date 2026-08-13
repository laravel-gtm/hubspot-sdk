<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\BatchReadAssociationsRequest;
use LaravelGtm\HubspotSdk\Responses\BatchAssociationsResponse;

it('resolves the v4 batch read endpoint', function (): void {
    $request = new BatchReadAssociationsRequest('2-61391055', 'contacts', ['1', '2']);
    expect($request->resolveEndpoint())->toBe('/crm/v4/associations/2-61391055/contacts/batch/read');
});

it('builds inputs from object ids', function (): void {
    $request = new BatchReadAssociationsRequest('2-63308387', 'companies', ['11', '22', '33']);

    $method = new ReflectionMethod(BatchReadAssociationsRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body['inputs'])->toBe([
        ['id' => '11'],
        ['id' => '22'],
        ['id' => '33'],
    ]);
});

it('rejects more than 100 ids', function (): void {
    $ids = array_map(strval(...), range(1, 101));

    new BatchReadAssociationsRequest('2-61391055', 'contacts', $ids);
})->throws(InvalidArgumentException::class, 'at most 100');

it('parses batch results and maps to ids by from id', function (): void {
    $response = BatchAssociationsResponse::fromArray([
        'status' => 'COMPLETE',
        'results' => [
            [
                'from' => ['id' => '101'],
                'to' => [
                    [
                        'toObjectId' => 9001,
                        'associationTypes' => [
                            ['category' => 'USER_DEFINED', 'typeId' => 17, 'label' => 'MQL to Contact'],
                        ],
                    ],
                ],
            ],
            [
                'from' => ['id' => '102'],
                'to' => [
                    ['toObjectId' => 9002, 'associationTypes' => []],
                    ['toObjectId' => 9003, 'associationTypes' => []],
                ],
            ],
        ],
    ]);

    expect($response->status)->toBe('COMPLETE');
    expect($response->results)->toHaveCount(2);
    expect($response->results[0]->fromId)->toBe('101');
    expect($response->results[0]->to[0]->toObjectId)->toBe('9001');
    expect($response->results[0]->to[0]->associationTypes[0]->typeId)->toBe(17);
    expect($response->toIdsByFromId())->toBe([
        '101' => ['9001'],
        '102' => ['9002', '9003'],
    ]);
});
