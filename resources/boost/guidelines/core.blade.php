## HubSpot SDK (`laravel-gtm/hubspot-sdk`)

This package provides a PHP SDK for the [HubSpot](https://developers.hubspot.com/) CRM API, built on Saloon 4.0. It wraps HubSpot CRM endpoints with typed request classes, response DTOs, search support, and OAuth per-user token integration.

### Setup

Add your API key to `.env`:

```
HUBSPOT_API_KEY=your-api-key
HUBSPOT_BASE_URL=https://api.hubapi.com
```

Publish the config file if you need to customize defaults:

```bash
php artisan vendor:publish --tag=hubspot-config
```

### Usage

- **`HubspotSdk`** is the main entry point. Inject it via the Laravel container or use `HubspotSdk::make()` for standalone use.
- All CRM methods are called directly on the SDK instance (e.g., `$sdk->getContact()`, `$sdk->searchDeals()`).
- Use `$sdk->forUser($user)` to create an OAuth-scoped instance with a per-user token.
- Request classes live under `src/Requests/` — GET requests use `defaultQuery()`, POST search requests use `HasJsonBody` with `defaultBody()`.
- Responses are immutable DTOs with typed properties — access data directly via properties.

### SDK Methods

@verbatim
<code-snippet name="Using SDK methods" lang="php">
use LaravelGtm\HubspotSdk\HubspotSdk;

$sdk = app(HubspotSdk::class);

// Get a contact with properties and associations
$contact = $sdk->getContact('501', properties: ['email', 'firstname'], associations: ['deals']);
$contact->properties['email']; // 'jane@example.com'
$contact->associations['deals']; // Association[]

// Search companies with filter groups
$companies = $sdk->searchCompanies(
    filterGroups: [
        [
            'filters' => [
                ['propertyName' => 'domain', 'operator' => 'EQ', 'value' => 'example.com'],
            ],
        ],
    ],
    properties: ['name', 'domain'],
);
$companies->total; // total matching count

// List owners
$owners = $sdk->listOwners(email: 'owner@example.com');

// OAuth per-user token
$userSdk = $sdk->forUser($user);
$userContacts = $userSdk->listContacts();
</code-snippet>
@endverbatim

### Response DTOs

All DTOs are `readonly`, implement `\JsonSerializable`, and have static `fromArray()` factory methods:

- **CRM objects** (`Contact`, `Deal`, `Company`): `id`, `properties` (key-value array), `createdAt`, `updatedAt`, `archived`.
- **Get responses** (`GetContactResponse`, `GetDealResponse`, `GetCompanyResponse`): same fields plus `associations` keyed by object type.
- **List responses** (`ListContactsResponse`, `ListDealsResponse`, etc.): `results` array + `Paging` DTO with `hasNextPage()` and `nextAfter`.
- **Search responses** (`SearchContactsResponse`, `SearchDealsResponse`, `SearchCompaniesResponse`): adds `total` count to the list pattern.
- **`Owner`**: `id`, `email`, `firstName`, `lastName`, `userId`, `archived`.
- **`CrmProperty`**: `name`, `label`, `fieldType`, `type`, `groupName`, `options`.

### Pagination

List and search endpoints use cursor-based pagination:

@verbatim
<code-snippet name="Paginating results" lang="php">
use LaravelGtm\HubspotSdk\HubspotSdk;

$sdk = app(HubspotSdk::class);
$after = null;

do {
    $response = $sdk->listContacts(limit: 100, after: $after);

    foreach ($response->results as $contact) {
        // $contact is a Contact DTO
    }

    $after = $response->paging->nextAfter;
} while ($response->paging->hasNextPage());
</code-snippet>
@endverbatim

### Testing

Use Saloon's `MockClient` and `MockResponse` for testing. Never make real API calls in tests:

@verbatim
<code-snippet name="Mocking HubSpot API calls" lang="php">
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;

$mockClient = new MockClient([
    GetContactRequest::class => MockResponse::make([
        'id' => '501',
        'properties' => ['email' => 'jane@example.com', 'firstname' => 'Jane'],
        'createdAt' => '2023-01-01T00:00:00.000Z',
        'updatedAt' => '2023-06-15T12:00:00.000Z',
        'archived' => false,
    ]),
]);

$connector->withMockClient($mockClient);
</code-snippet>
@endverbatim

### Important Notes

- Rate limits are enforced automatically: 150 requests per 10 seconds (burst) and 1M per day. In Laravel, rate limit state is stored in the application cache.
- All response DTOs are `readonly` with `fromArray()` factory methods. Properties are `array<string, string|null>` key-value pairs.
- Cursor-based pagination: check `$response->paging->hasNextPage()` and pass `$response->paging->nextAfter` as the `after` parameter.
- Search methods accept HubSpot filter groups: filters within a group are ANDed, groups are ORed.
- Use `$sdk->forUser($user)` for OAuth per-user tokens. Configure `hubspot.oauth.token_column` in `config/hubspot.php`.
- Run `vendor/bin/pint` and `phpstan` before shipping changes; see the package `composer.json` scripts.
