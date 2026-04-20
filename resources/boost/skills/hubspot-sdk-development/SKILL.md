---
name: hubspot-sdk-development
description: Build features using the HubSpot CRM SDK, including contacts, deals, companies, owners, search, associations, and property definitions.
---

# HubSpot SDK Development

## When to use this skill

Use this skill when working with the `laravel-gtm/hubspot-sdk` package to interact with the HubSpot CRM API. This includes fetching and searching contacts, deals, and companies; retrieving associations between objects; listing CRM property definitions; working with owners; and using OAuth per-user tokens.

## SDK entry point

Inject `HubspotSdk` or use the static factory:

```php
use LaravelGtm\HubspotSdk\HubspotSdk;

// Via Laravel container (recommended)
$sdk = app(HubspotSdk::class);

// Standalone
$sdk = HubspotSdk::make(
    baseUrl: 'https://api.hubapi.com',
    token: 'your-api-token',
);

// OAuth — authenticated as a specific user
$userSdk = $sdk->forUser($authenticatedUser);
```

## Connector

`HubspotConnector` resolves the base URL (defaults to `https://api.hubapi.com`), sets JSON headers, and authenticates with `TokenAuthenticator` (Bearer token). It is configured via `config/hubspot.php` and `HUBSPOT_*` env vars.

Key behaviors:
- **Auth**: `TokenAuthenticator` (Bearer) — set via config `hubspot.api_key` or constructor
- **Rate limits**: 150 requests per 10 seconds (burst), 1,000,000 per day (daily)
- **Retry**: 3 tries, 500ms interval, exponential backoff
- **Timeouts**: 10s connect, 30s request
- **Errors**: `AlwaysThrowOnErrors` trait — all 4xx/5xx responses throw immediately
- **429 handling**: Respects `Retry-After` header, defaults to 10s release

## Methods reference

All methods are called directly on `HubspotSdk`. They send a request via the connector and return typed DTOs via `dtoOrFail()`.

### Contacts

```php
// Get a single contact with optional properties and associations
$contact = $sdk->getContact(
    contactId: '501',
    properties: ['email', 'firstname', 'lastname'],
    associations: ['deals', 'companies'],
);
$contact->id;                    // '501'
$contact->properties['email'];   // 'jane@example.com'
$contact->associations['deals']; // Association[]
$contact->createdAt;             // '2023-01-01T00:00:00.000Z'
$contact->archived;              // false

// List contacts with pagination
$response = $sdk->listContacts(
    limit: 10,
    properties: ['email', 'firstname'],
);
foreach ($response->results as $contact) {
    $contact->id;
    $contact->properties['email'];
}
if ($response->paging->hasNextPage()) {
    $next = $sdk->listContacts(limit: 10, after: $response->paging->nextAfter);
}

// Search contacts with filter groups
$response = $sdk->searchContacts(
    filterGroups: [
        [
            'filters' => [
                ['propertyName' => 'email', 'operator' => 'CONTAINS_TOKEN', 'value' => '*@example.com'],
            ],
        ],
    ],
    properties: ['firstname', 'lastname', 'email'],
    limit: 50,
);
$response->total;   // total matching count
$response->results; // Contact[]

// List contact property definitions
$props = $sdk->listContactProperties();
foreach ($props->results as $prop) {
    $prop->name;      // 'email'
    $prop->label;     // 'Email'
    $prop->fieldType; // 'text'
    $prop->type;      // 'string'
    $prop->options;   // CrmPropertyOption[]
}
```

### Deals

```php
// Get a single deal with associations
$deal = $sdk->getDeal(
    dealId: '57030476464',
    properties: ['dealname', 'amount', 'dealstage'],
    associations: ['contacts', 'companies'],
);
$deal->id;
$deal->properties['dealname'];
$deal->associations['contacts']; // Association[]

// List deals with pagination
$response = $sdk->listDeals(limit: 10, after: $cursor);
foreach ($response->results as $deal) {
    $deal->properties['amount'];
}

// Search deals with filter groups and sorting
$response = $sdk->searchDeals(
    filterGroups: [
        [
            'filters' => [
                ['propertyName' => 'dealstage', 'operator' => 'EQ', 'value' => 'closedwon'],
            ],
        ],
    ],
    properties: ['dealname', 'amount', 'closedate'],
    sorts: [['propertyName' => 'closedate', 'direction' => 'DESCENDING']],
    limit: 20,
);

// List deal property definitions
$props = $sdk->listDealProperties();
```

### Companies

```php
// Get a single company with associations
$company = $sdk->getCompany(
    companyId: '20787072317',
    properties: ['name', 'domain', 'industry'],
    associations: ['contacts'],
);
$company->properties['domain'];
$company->associations['contacts']; // Association[]

// Search companies
$response = $sdk->searchCompanies(
    filterGroups: [
        [
            'filters' => [
                ['propertyName' => 'domain', 'operator' => 'EQ', 'value' => 'example.com'],
            ],
        ],
    ],
    properties: ['name', 'domain'],
);
```

### Associations

```php
// Get contacts associated with a company
$response = $sdk->getCompanyContactAssociations(
    companyId: '20787072317',
    limit: 100,
);
foreach ($response->results as $assoc) {
    $assoc->id;   // associated contact ID
    $assoc->type; // 'company_to_contact'
}
if ($response->paging->hasNextPage()) {
    $next = $sdk->getCompanyContactAssociations(
        companyId: '20787072317',
        after: $response->paging->nextAfter,
    );
}
```

### Owners

```php
// Get a single owner
$owner = $sdk->getOwner('87644597');
$owner->id;        // '87644597'
$owner->email;     // 'owner@example.com'
$owner->firstName; // 'Jane'
$owner->lastName;  // 'Smith'

// List owners, optionally filtered by email
$response = $sdk->listOwners(email: 'owner@example.com');
foreach ($response->results as $owner) {
    $owner->email;
}
```

## Response DTOs

All DTOs are `readonly` classes implementing `\JsonSerializable` with static `fromArray()` factory methods.

### CRM object DTOs

`Contact`, `Deal`, and `Company` share the same shape:
- `id` (string) — HubSpot object ID
- `properties` (array&lt;string, string|null&gt;) — key-value property map
- `createdAt`, `updatedAt` (string) — ISO 8601 timestamps
- `archived` (bool), `archivedAt` (?string)

### Get responses

`GetContactResponse`, `GetDealResponse`, `GetCompanyResponse` extend the CRM object fields with:
- `associations` (array&lt;string, list&lt;Association&gt;&gt;) — keyed by object type (e.g., `'deals'`, `'contacts'`)

Each `Association` has `id` (string) and `type` (string).

### List responses

`ListContactsResponse`, `ListDealsResponse`, `ListOwnersResponse`, `AssociationListResponse` contain:
- `results` — typed array of the relevant DTO
- `paging` — `Paging` DTO with `nextAfter`, `nextLink`, and `hasNextPage()` helper

### Search responses

`SearchContactsResponse`, `SearchDealsResponse`, `SearchCompaniesResponse` contain:
- `total` (int) — total number of matching records
- `results` — typed array of CRM object DTOs
- `paging` — `Paging` DTO

### Property DTOs

`CrmProperty` describes a CRM property definition:
- `name`, `label`, `description`, `fieldType`, `groupName`, `type` (all strings)
- `options` (list&lt;CrmPropertyOption&gt;) — each with `label`, `value`, `description`, `hidden`, `displayOrder`

`ListContactPropertiesResponse` and `ListDealPropertiesResponse` wrap a `results` array of `CrmProperty`.

### Owner DTO

`Owner` has: `id`, `email`, `firstName`, `lastName`, `userId`, `archived`, `createdAt`, `updatedAt`.

## Search filter groups

The `searchContacts()`, `searchDeals()`, and `searchCompanies()` methods accept HubSpot filter groups:

```php
$filterGroups = [
    // Group 1 (ORed with other groups)
    [
        'filters' => [
            // Filters within a group are ANDed
            ['propertyName' => 'email', 'operator' => 'CONTAINS_TOKEN', 'value' => '*@example.com'],
            ['propertyName' => 'firstname', 'operator' => 'HAS_PROPERTY'],
        ],
    ],
    // Group 2 (ORed with group 1)
    [
        'filters' => [
            ['propertyName' => 'hs_lead_status', 'operator' => 'EQ', 'value' => 'NEW'],
        ],
    ],
];
```

- **Within a group**: filters are ANDed (all must match)
- **Across groups**: groups are ORed (any group can match)
- Common operators: `EQ`, `NEQ`, `LT`, `LTE`, `GT`, `GTE`, `HAS_PROPERTY`, `NOT_HAS_PROPERTY`, `CONTAINS_TOKEN`, `NOT_CONTAINS_TOKEN`

## Pagination pattern

All list endpoints use cursor-based pagination with the `after` parameter:

```php
$after = null;

do {
    $response = $sdk->listContacts(limit: 100, after: $after);

    foreach ($response->results as $contact) {
        // Process contact
    }

    $after = $response->paging->nextAfter;
} while ($response->paging->hasNextPage());
```

Search endpoints follow the same pattern — pass `after` and check `$response->paging->hasNextPage()`.

## OAuth / per-user tokens

The SDK supports per-user OAuth tokens via the `forUser()` method:

```php
// Service-level token (from config)
$sdk = app(HubspotSdk::class);

// User-level OAuth token — reads from the configured column on the user model
$userSdk = $sdk->forUser(auth()->user());
$contacts = $userSdk->listContacts();
```

Configure the user model and token column in `config/hubspot.php`:

```php
'oauth' => [
    'user_model' => env('HUBSPOT_USER_MODEL', 'App\\Models\\User'),
    'token_column' => env('HUBSPOT_OAUTH_TOKEN_COLUMN', 'hubspot_access_token'),
],
```

The user must be an Eloquent `Model` instance. The method throws `\RuntimeException` if the token column is empty or missing.

## Rate limiting

The SDK enforces HubSpot API rate limits automatically via Saloon:

- **Burst**: 150 requests per 10 seconds
- **Daily**: 1,000,000 requests per day

In Laravel, rate limit state is stored in the application cache (`LaravelCacheStore`). In standalone mode, it uses an in-memory store. Limits are configurable via env vars `HUBSPOT_RATE_LIMIT_BURST` and `HUBSPOT_RATE_LIMIT_DAILY`.

When a 429 response is received, the SDK reads the `Retry-After` header and pauses accordingly (defaults to 10 seconds if the header is missing).

## Laravel integration

The service provider merges config from `config/hubspot.php` under the `hubspot` key, registers `HubspotConnector` and `HubspotSdk` as singletons, and publishes the config file:

```bash
php artisan vendor:publish --tag=hubspot-config
```

Environment variables:

```
HUBSPOT_BASE_URL=https://api.hubapi.com
HUBSPOT_API_KEY=your-api-key
HUBSPOT_OAUTH_TOKEN_COLUMN=hubspot_access_token
HUBSPOT_RATE_LIMIT_BURST=150
HUBSPOT_RATE_LIMIT_DAILY=1000000
```

## Testing

Use Saloon's mock system. Never make real API calls in tests:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;

it('fetches a contact', function () {
    $connector = new HubspotConnector('https://api.hubapi.com', 'test-token');
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

    $sdk = new HubspotSdk($connector);
    $contact = $sdk->getContact('501');

    expect($contact->id)->toBe('501');
    expect($contact->properties['email'])->toBe('jane@example.com');
    $mockClient->assertSent(GetContactRequest::class);
});
```

The test suite uses Pest + Orchestra Testbench. `Config::preventStrayRequests()` is enabled in `tests/Pest.php` to catch accidental real API calls.

## Conventions

- PHP 8.4+, `declare(strict_types=1)`.
- PHPStan level 8 (see `phpstan.neon.dist`).
- Format with Laravel Pint via `composer format` / `composer lint`.
- Follow project rules under `.claude/rules/` and `.cursor/rules/` (Saloon, PHPStan, Laravel package).
