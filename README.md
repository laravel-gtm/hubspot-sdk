# HubSpot SDK

A Laravel-ready PHP SDK for the HubSpot CRM API, built with [Saloon](https://docs.saloon.dev/) v4. Includes token auth, rate limiting, and typed response DTOs.

## Requirements

- PHP `^8.4`
- Laravel `^11.0 || ^12.0 || ^13.0` (for the optional Laravel integration)

## Supported Endpoints

| SDK Method | HTTP | API Endpoint | Since |
|-----------|------|-------------|-------|
| `getContact()` | GET | `/crm/v3/objects/contacts/{contactId}` | unreleased |
| `listContacts()` | GET | `/crm/v3/objects/contacts` | unreleased |
| `listContactProperties()` | GET | `/crm/v3/properties/contact` | unreleased |
| `getDeal()` | GET | `/crm/v3/objects/deals/{dealId}` | unreleased |
| `listDeals()` | GET | `/crm/v3/objects/deals` | v0.0.1 |
| `listDealProperties()` | GET | `/crm/v3/properties/deal` | v0.0.3 |

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

## Development

```bash
composer test        # Pest
composer analyse     # PHPStan
composer lint        # Pint (check)
composer format      # Pint (fix)
```

## License

MIT. See [LICENSE](LICENSE).
