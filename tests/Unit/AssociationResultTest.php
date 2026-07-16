<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Responses\AssociationResult;

it('parses the batch-style results envelope', function (): void {
    $result = AssociationResult::fromArray([
        'results' => [
            [
                'fromObjectTypeId' => '0-1',
                'fromObjectId' => '501',
                'toObjectTypeId' => '0-2',
                'toObjectId' => '20787072317',
                'labels' => [],
            ],
        ],
    ]);

    expect($result->fromObjectId)->toBe('501');
    expect($result->toObjectId)->toBe('20787072317');
    expect($result->labels)->toBe([]);
});

it('parses a flat association object without a results envelope', function (): void {
    $result = AssociationResult::fromArray([
        'fromObjectId' => '501',
        'toObjectId' => '20787072317',
        'labels' => ['Point of contact'],
    ]);

    expect($result->fromObjectId)->toBe('501');
    expect($result->toObjectId)->toBe('20787072317');
    expect($result->labels)->toBe(['Point of contact']);
});
