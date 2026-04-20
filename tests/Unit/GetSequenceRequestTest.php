<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\GetSequenceRequest;
use LaravelGtm\HubspotSdk\Responses\Sequence;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('resolves the single-sequence endpoint with id', function (): void {
    $request = new GetSequenceRequest(sequenceId: '987', userId: '2222222');

    expect($request->resolveEndpoint())->toBe('/automation/sequences/2026-03/987');
});

it('always includes userId in query', function (): void {
    $request = new GetSequenceRequest(sequenceId: '987', userId: '2222222');

    $method = new ReflectionMethod(GetSequenceRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe(['userId' => '2222222']);
});

it('parses single-sequence response with nested steps', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        GetSequenceRequest::class => MockResponse::make([
            'id' => '987',
            'name' => 'PQL Outbound',
            'userId' => '2222222',
            'createdAt' => '2026-02-01T00:00:00Z',
            'updatedAt' => '2026-04-10T00:00:00Z',
            'steps' => [
                [
                    'id' => 'step-0',
                    'stepOrder' => 0,
                    'delayMillis' => 604800000,
                    'actionType' => 'AUTOMATED_EMAIL',
                ],
                [
                    'id' => 'step-1',
                    'stepOrder' => 1,
                    'delayMillis' => 604800000,
                    'actionType' => 'AUTOMATED_EMAIL',
                ],
            ],
        ], 200),
    ]));

    $response = $connector->send(new GetSequenceRequest('987', '2222222'))->dtoOrFail();

    expect($response)->toBeInstanceOf(Sequence::class);
    expect($response->id)->toBe('987');
    expect($response->name)->toBe('PQL Outbound');
    expect($response->steps)->toHaveCount(2);
    expect($response->steps[0]->stepOrder)->toBe(0);
    expect($response->steps[0]->delayMillis)->toBe(604800000);
    expect($response->steps[0]->actionType)->toBe('AUTOMATED_EMAIL');
});
