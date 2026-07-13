# HubSpot SDK

A Laravel-ready PHP SDK for the HubSpot CRM API, built with [Saloon](https://docs.saloon.dev/) v4. Includes token auth, rate limiting, and typed response DTOs.

## Requirements

- PHP `^8.4`
- Laravel `^11.0 || ^12.0 || ^13.0` (for the optional Laravel integration)

## Supported Endpoints

| SDK Method | HTTP | API Endpoint | Since |
|-----------|------|-------------|-------|
| `createContact()` | POST | `/crm/v3/objects/contacts` | unreleased |
| `createCompany()` | POST | `/crm/v3/objects/companies` | unreleased |
| `updateCompany()` | PATCH | `/crm/v3/objects/companies/{companyId}` | unreleased |
| `getContact()` | GET | `/crm/v3/objects/contacts/{contactId}` | v0.0.6 |
| `listContacts()` | GET | `/crm/v3/objects/contacts` | v0.0.6 |
| `listContactProperties()` | GET | `/crm/v3/properties/contact` | v0.0.6 |
| `updateContact()` | PATCH | `/crm/v3/objects/contacts/{contactId}` | v0.0.11 |
| `getCompany()` | GET | `/crm/v3/objects/companies/{companyId}` | v0.0.9 |
| `getCompanyContactAssociations()` | GET | `/crm/v3/objects/companies/{companyId}/associations/contacts` | v0.0.7 |
| `searchCompanies()` | POST | `/crm/v3/objects/companies/search` | v0.0.7 |
| `searchContacts()` | POST | `/crm/v3/objects/contacts/search` | v0.0.9 |
| `searchDeals()` | POST | `/crm/v3/objects/deals/search` | v0.0.9 |
| `getDeal()` | GET | `/crm/v3/objects/deals/{dealId}` | v0.0.5 |
| `listDeals()` | GET | `/crm/v3/objects/deals` | v0.0.1 |
| `listDealProperties()` | GET | `/crm/v3/properties/deal` | v0.0.3 |
| `getOwner()` | GET | `/crm/v3/owners/{ownerId}` | v0.0.7 |
| `listOwners()` | GET | `/crm/v3/owners` | v0.0.7 |
| `listSequences()` | GET | `/automation/sequences/2026-03` | v0.0.10 |
| `getSequence()` | GET | `/automation/sequences/2026-03/{sequenceId}` | v0.0.10 |
| `enrollContactInSequence()` | POST | `/automation/sequences/2026-03/enrollments` | v0.0.10 |

## Installation

```bash
composer require laravel-gtm/hubspot-sdk
```

## Configuration (Laravel)

Publish the config (before init the tag is `hubspot-sdk-config`; after init it becomes `{your-package-slug}-config`):

```bash
php artisan vendor:publish --tag=hubspot-sdk-config
```

After running the init script, use your package slug in the tag (e.g. `hubspot-sdk-config`). Env keys use your chosen `ENV_PREFIX` (defaults before init use `HUBSPOT_*`):

- `HUBSPOT_BASE_URL`
- `HUBSPOT_TOKEN`
- `HUBSPOT_AUTH_HEADER`

## Usage

### Via the service container

```php
use LaravelGtm\HubspotSdk\HubspotSdk;

$sdk = app(HubspotSdk::class);
```

### Standalone

```php
use LaravelGtm\HubspotSdk\HubspotSdk;

$sdk = HubspotSdk::make(
    baseUrl: 'claude',
    token: 'your-token',
);
```

### Alerting on expired tokens

Register a callback invoked whenever a request fails with a 401 Unauthorized response,
before the exception propagates. Useful for alerting on expired or revoked OAuth tokens.

```php
$sdk->onUnauthorized(function (\Saloon\Http\Response $response): void {
    // e.g. notify, log, or flag the user's connection as needing reauthorization
});
```

## Development

```bash
composer test        # Pest
composer analyse     # PHPStan
composer lint        # Pint (check)
composer format      # Pint (fix)
```

## License

MIT. See [LICENSE](LICENSE).
