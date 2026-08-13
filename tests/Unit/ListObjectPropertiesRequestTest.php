<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\ListObjectPropertiesRequest;
use LaravelGtm\HubspotSdk\Responses\CrmProperty;
use LaravelGtm\HubspotSdk\Responses\ListObjectPropertiesResponse;

it('resolves the properties endpoint for a custom object type id', function (): void {
    $request = new ListObjectPropertiesRequest('2-63308387');
    expect($request->resolveEndpoint())->toBe('/crm/v3/properties/2-63308387');
});

it('resolves the properties endpoint for a named object type', function (): void {
    $request = new ListObjectPropertiesRequest('contacts');
    expect($request->resolveEndpoint())->toBe('/crm/v3/properties/contacts');
});

it('builds query with archived and includeHidden as strings', function (): void {
    $request = new ListObjectPropertiesRequest('2-61391055', archived: false, includeHidden: true);

    $method = new ReflectionMethod(ListObjectPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query['archived'])->toBe('false');
    expect($query['includeHidden'])->toBe('true');
});

it('omits null fields from query', function (): void {
    $request = new ListObjectPropertiesRequest('2-61391055');

    $method = new ReflectionMethod(ListObjectPropertiesRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe([]);
});

it('parses property definitions including enum options', function (): void {
    $response = ListObjectPropertiesResponse::fromArray([
        'results' => [
            [
                'name' => 'lead_disqualification_reason',
                'label' => 'Lead Disqualification Reason',
                'type' => 'enumeration',
                'fieldType' => 'select',
                'groupName' => 'sales_properties',
                'options' => [
                    ['label' => 'Bad Timing', 'value' => 'Bad Timing'],
                    ['label' => 'Unresponsive', 'value' => 'Unresponsive'],
                ],
            ],
        ],
    ]);

    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(CrmProperty::class);
    expect($response->results[0]->name)->toBe('lead_disqualification_reason');
    expect($response->results[0]->options)->toHaveCount(2);
});
