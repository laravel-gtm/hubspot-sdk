<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\UpdateDealRequest;
use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use Saloon\Enums\Method;
use Saloon\Exceptions\Request\Statuses\BadRequestException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

/**
 * @param  array<string, string>  $properties
 * @return array<string, mixed>
 */
function dealPayload(array $properties): array
{
    return [
        'id' => '57030476464',
        'properties' => $properties,
        'createdAt' => '2023-01-01T00:00:00.000Z',
        'updatedAt' => '2026-11-10T12:00:00.000Z',
        'archived' => false,
    ];
}

it('uses the PATCH method', function (): void {
    $request = new UpdateDealRequest('57030476464', ['amount' => '100']);

    expect($request->getMethod())->toBe(Method::PATCH);
});

it('resolves the deal endpoint with id', function (): void {
    $request = new UpdateDealRequest('57030476464', ['amount' => '100']);

    expect($request->resolveEndpoint())->toBe('/crm/v3/objects/deals/57030476464');
});

it('sends the mixed-case forecast category and decimal-string amount unchanged', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        UpdateDealRequest::class => MockResponse::make(dealPayload([
            'hs_manual_forecast_category' => 'Forecast',
            'amount' => '24000.50',
        ])),
    ]);
    $connector->withMockClient($mockClient);

    $response = (new HubspotSdk($connector))->updateDeal('57030476464', [
        'hs_manual_forecast_category' => 'Forecast',
        'amount' => '24000.50',
    ]);

    expect($response)->toBeInstanceOf(GetDealResponse::class)
        ->and($response->id)->toBe('57030476464')
        ->and($response->properties['hs_manual_forecast_category'])->toBe('Forecast')
        ->and($response->properties['amount'])->toBe('24000.50');

    $mockClient->assertSentCount(1);
    $sent = $mockClient->getLastResponse()?->getPsrRequest();

    if ($sent === null) {
        throw new RuntimeException('Expected an UpdateDealRequest to have been sent.');
    }

    // Assert on the exact JSON sent: the enum keeps its case and the amount
    // stays a string ("24000.50"), not a number (24000.5).
    expect($sent->getMethod())->toBe('PATCH')
        ->and((string) $sent->getUri())->toBe('https://api.hubapi.com/crm/v3/objects/deals/57030476464')
        ->and((string) $sent->getBody())
        ->toBe('{"properties":{"hs_manual_forecast_category":"Forecast","amount":"24000.50"}}');
});

it('sends only the properties that were passed', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        UpdateDealRequest::class => MockResponse::make(dealPayload([
            'dealname' => 'Big Deal',
            'amount' => '50000',
            'hs_manual_forecast_category' => 'COMMIT',
        ])),
    ]);
    $connector->withMockClient($mockClient);

    (new HubspotSdk($connector))->updateDeal('57030476464', ['hs_manual_forecast_category' => 'COMMIT']);

    $mockClient->assertSent(fn (UpdateDealRequest $request): bool => $request->body()->all() === [
        'properties' => ['hs_manual_forecast_category' => 'COMMIT'],
    ]);
});

it('throws the matching Saloon exception for client errors, like other requests', function (int $status, string $exception): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $connector->retryInterval = 0;
    $mockClient = new MockClient([
        UpdateDealRequest::class => MockResponse::make(['status' => 'error', 'message' => 'nope'], $status),
    ]);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HubspotSdk($connector))->updateDeal('57030476464', ['amount' => '1']))
        ->toThrow($exception);

    // Retried like every other non-create request (connector $tries = 3).
    $mockClient->assertSentCount(3);
})->with([
    'invalid property value' => [400, BadRequestException::class],
    'missing scope' => [403, ForbiddenException::class],
    'unknown deal' => [404, NotFoundException::class],
]);

it('waits and retries after a 429, like other requests', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');

    $attempt = 0;
    $mockClient = new MockClient([
        UpdateDealRequest::class => function () use (&$attempt): MockResponse {
            $attempt++;

            return $attempt === 1
                ? MockResponse::make(['message' => 'rate limited'], 429, ['Retry-After' => '1'])
                : MockResponse::make(dealPayload(['amount' => '24000.50']));
        },
    ]);
    $connector->withMockClient($mockClient);

    $response = (new HubspotSdk($connector))->updateDeal('57030476464', ['amount' => '24000.50']);

    expect($response->properties['amount'])->toBe('24000.50')
        ->and($attempt)->toBe(2);
})->group('slow');
