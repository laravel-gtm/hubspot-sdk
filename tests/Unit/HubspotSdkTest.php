<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\GetCompanyContactAssociationsRequest;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;
use LaravelGtm\HubspotSdk\Requests\GetDealRequest;
use LaravelGtm\HubspotSdk\Requests\GetOwnerRequest;
use LaravelGtm\HubspotSdk\Requests\ListContactPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListContactsRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;
use LaravelGtm\HubspotSdk\Requests\ListOwnersRequest;
use LaravelGtm\HubspotSdk\Requests\SearchCompaniesRequest;
use LaravelGtm\HubspotSdk\Responses\Association;
use LaravelGtm\HubspotSdk\Responses\AssociationListResponse;
use LaravelGtm\HubspotSdk\Responses\Company;
use LaravelGtm\HubspotSdk\Responses\Contact;
use LaravelGtm\HubspotSdk\Responses\CrmProperty;
use LaravelGtm\HubspotSdk\Responses\Deal;
use LaravelGtm\HubspotSdk\Responses\GetContactResponse;
use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use LaravelGtm\HubspotSdk\Responses\ListContactPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListContactsResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use LaravelGtm\HubspotSdk\Responses\ListOwnersResponse;
use LaravelGtm\HubspotSdk\Responses\Owner;
use LaravelGtm\HubspotSdk\Responses\SearchCompaniesResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns a single contact with associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetContactRequest::class => MockResponse::make([
            'id' => '501',
            'properties' => ['email' => 'jane@example.com', 'firstname' => 'Jane'],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
            'archived' => false,
            'associations' => [
                'deals' => [
                    'results' => [
                        ['id' => '101', 'type' => 'contact_to_deal'],
                    ],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getContact('501', associations: ['deals']);

    expect($response)->toBeInstanceOf(GetContactResponse::class);
    expect($response->id)->toBe('501');
    expect($response->properties['email'])->toBe('jane@example.com');
    expect($response->associations)->toHaveKey('deals');
    expect($response->associations['deals'])->toHaveCount(1);
    expect($response->associations['deals'][0])->toBeInstanceOf(Association::class);

    $mockClient->assertSent(GetContactRequest::class);
});

it('returns a list contacts response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListContactsRequest::class => MockResponse::make([
            'results' => [
                [
                    'id' => '501',
                    'properties' => ['email' => 'jane@example.com', 'firstname' => 'Jane'],
                    'createdAt' => '2023-01-01T00:00:00.000Z',
                    'updatedAt' => '2023-06-15T12:00:00.000Z',
                    'archived' => false,
                ],
            ],
            'paging' => [
                'next' => [
                    'after' => '502',
                    'link' => 'https://api.hubapi.com/crm/v3/objects/contacts?after=502',
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listContacts(limit: 10);

    expect($response)->toBeInstanceOf(ListContactsResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(Contact::class);
    expect($response->results[0]->id)->toBe('501');
    expect($response->results[0]->properties['email'])->toBe('jane@example.com');
    expect($response->paging->nextAfter)->toBe('502');
    expect($response->paging->hasNextPage())->toBeTrue();

    $mockClient->assertSent(ListContactsRequest::class);
});

it('returns a list contact properties response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListContactPropertiesRequest::class => MockResponse::make([
            'results' => [
                [
                    'name' => 'email',
                    'label' => 'Email',
                    'type' => 'string',
                    'fieldType' => 'text',
                    'groupName' => 'contactinformation',
                    'description' => 'The contact email',
                    'options' => [],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listContactProperties();

    expect($response)->toBeInstanceOf(ListContactPropertiesResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(CrmProperty::class);
    expect($response->results[0]->name)->toBe('email');

    $mockClient->assertSent(ListContactPropertiesRequest::class);
});

it('returns a single deal with associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetDealRequest::class => MockResponse::make([
            'id' => '57030476464',
            'properties' => ['dealname' => 'Big Deal', 'amount' => '50000'],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
            'archived' => false,
            'associations' => [
                'contacts' => [
                    'results' => [
                        ['id' => '101', 'type' => 'deal_to_contact'],
                        ['id' => '102', 'type' => 'deal_to_contact'],
                    ],
                ],
                'companies' => [
                    'results' => [
                        ['id' => '201', 'type' => 'deal_to_company'],
                    ],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getDeal('57030476464', associations: ['contacts', 'companies']);

    expect($response)->toBeInstanceOf(GetDealResponse::class);
    expect($response->id)->toBe('57030476464');
    expect($response->properties['dealname'])->toBe('Big Deal');
    expect($response->associations)->toHaveKeys(['contacts', 'companies']);
    expect($response->associations['contacts'])->toHaveCount(2);
    expect($response->associations['contacts'][0])->toBeInstanceOf(Association::class);
    expect($response->associations['contacts'][0]->id)->toBe('101');
    expect($response->associations['companies'])->toHaveCount(1);

    $mockClient->assertSent(GetDealRequest::class);
});

it('returns a single deal without associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetDealRequest::class => MockResponse::make([
            'id' => '123',
            'properties' => ['dealname' => 'Simple Deal'],
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
            'archived' => false,
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getDeal('123');

    expect($response)->toBeInstanceOf(GetDealResponse::class);
    expect($response->id)->toBe('123');
    expect($response->associations)->toBeEmpty();

    $mockClient->assertSent(GetDealRequest::class);
});

it('returns a list deals response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealsRequest::class => MockResponse::make([
            'results' => [
                [
                    'id' => '123',
                    'properties' => ['dealname' => 'Test Deal', 'amount' => '1000'],
                    'createdAt' => '2023-01-01T00:00:00.000Z',
                    'updatedAt' => '2023-06-15T12:00:00.000Z',
                    'archived' => false,
                ],
            ],
            'paging' => [
                'next' => [
                    'after' => '456',
                    'link' => 'https://api.hubapi.com/crm/v3/objects/deals?after=456',
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDeals(limit: 10);

    expect($response)->toBeInstanceOf(ListDealsResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(Deal::class);
    expect($response->results[0]->id)->toBe('123');
    expect($response->results[0]->properties['dealname'])->toBe('Test Deal');
    expect($response->paging->nextAfter)->toBe('456');
    expect($response->paging->hasNextPage())->toBeTrue();

    $mockClient->assertSent(ListDealsRequest::class);
});

it('returns deals with no next page', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealsRequest::class => MockResponse::make([
            'results' => [],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDeals();

    expect($response->results)->toBeEmpty();
    expect($response->paging->hasNextPage())->toBeFalse();
});

it('returns a list deal properties response', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListDealPropertiesRequest::class => MockResponse::make([
            'results' => [
                [
                    'name' => 'dealname',
                    'label' => 'Deal Name',
                    'type' => 'string',
                    'fieldType' => 'text',
                    'groupName' => 'dealinformation',
                    'description' => 'The name of the deal',
                    'options' => [],
                ],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listDealProperties(archived: false, dataSensitivity: 'non_sensitive');

    expect($response)->toBeInstanceOf(ListDealPropertiesResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(CrmProperty::class);
    expect($response->results[0]->name)->toBe('dealname');
    expect($response->results[0]->label)->toBe('Deal Name');

    $mockClient->assertSent(ListDealPropertiesRequest::class);
});

it('returns a search companies response with pagination', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        SearchCompaniesRequest::class => MockResponse::make([
            'total' => 150,
            'results' => [
                [
                    'id' => '20787072317',
                    'properties' => ['name' => 'Acme Corp', 'hubspot_owner_id' => '87644597'],
                    'createdAt' => '2023-01-01T00:00:00.000Z',
                    'updatedAt' => '2023-06-15T12:00:00.000Z',
                    'archived' => false,
                ],
            ],
            'paging' => [
                'next' => ['after' => '20787072318'],
            ],
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->searchCompanies(
        filterGroups: [['filters' => [['propertyName' => 'hubspot_owner_id', 'operator' => 'HAS_PROPERTY']]]],
        properties: ['name', 'hubspot_owner_id'],
        limit: 100,
    );

    expect($response)->toBeInstanceOf(SearchCompaniesResponse::class);
    expect($response->total)->toBe(150);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0])->toBeInstanceOf(Company::class);
    expect($response->results[0]->properties['name'])->toBe('Acme Corp');
    expect($response->paging->hasNextPage())->toBeTrue();
    expect($response->paging->nextAfter)->toBe('20787072318');

    $mockClient->assertSent(SearchCompaniesRequest::class);
});

it('returns company contact associations', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetCompanyContactAssociationsRequest::class => MockResponse::make([
            'results' => [
                ['id' => '501', 'type' => 'company_to_contact'],
                ['id' => '502', 'type' => 'company_to_contact'],
            ],
            'paging' => null,
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getCompanyContactAssociations('20787072317');

    expect($response)->toBeInstanceOf(AssociationListResponse::class);
    expect($response->results)->toHaveCount(2);
    expect($response->results[0])->toBeInstanceOf(Association::class);
    expect($response->results[0]->id)->toBe('501');
    expect($response->paging->hasNextPage())->toBeFalse();

    $mockClient->assertSent(GetCompanyContactAssociationsRequest::class);
});

it('returns an owner by id', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        GetOwnerRequest::class => MockResponse::make([
            'id' => '87644597',
            'email' => 'ethan@laravel.com',
            'firstName' => 'Ethan',
            'lastName' => 'Maestas',
            'userId' => '12345',
            'archived' => false,
            'createdAt' => '2023-01-01T00:00:00.000Z',
            'updatedAt' => '2023-06-15T12:00:00.000Z',
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->getOwner('87644597');

    expect($response)->toBeInstanceOf(Owner::class);
    expect($response->id)->toBe('87644597');
    expect($response->email)->toBe('ethan@laravel.com');
    expect($response->firstName)->toBe('Ethan');
    expect($response->lastName)->toBe('Maestas');

    $mockClient->assertSent(GetOwnerRequest::class);
});

it('returns owners filtered by email', function (): void {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
    $mockClient = new MockClient([
        ListOwnersRequest::class => MockResponse::make([
            'results' => [
                [
                    'id' => '87644597',
                    'email' => 'ethan@laravel.com',
                    'firstName' => 'Ethan',
                    'lastName' => 'Maestas',
                    'userId' => '12345',
                    'archived' => false,
                ],
            ],
            'paging' => null,
        ], 200),
    ]);
    $connector->withMockClient($mockClient);

    $sdk = new HubspotSdk($connector);
    $response = $sdk->listOwners(email: 'ethan@laravel.com');

    expect($response)->toBeInstanceOf(ListOwnersResponse::class);
    expect($response->results)->toHaveCount(1);
    expect($response->results[0]->email)->toBe('ethan@laravel.com');

    $mockClient->assertSent(ListOwnersRequest::class);
});
