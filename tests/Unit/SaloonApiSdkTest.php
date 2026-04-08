<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use YourVendor\SaloonApiSdk\Requests\ExampleGetRequest;
use YourVendor\SaloonApiSdk\Responses\ExampleResponse;
use YourVendor\SaloonApiSdk\SaloonApiSdk;
use YourVendor\SaloonApiSdk\SaloonConnector;

it('returns an example response from ping', function (): void {
    $connector = new SaloonConnector('https://example.test', 'test-token');
    $mockClient = new MockClient([
        ExampleGetRequest::class => MockResponse::make([
            'status' => 'ok',
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new SaloonApiSdk($connector);
    $response = $sdk->ping();

    expect($response)->toBeInstanceOf(ExampleResponse::class);
    expect($response->status)->toBe('ok');

    $mockClient->assertSent(ExampleGetRequest::class);
});
