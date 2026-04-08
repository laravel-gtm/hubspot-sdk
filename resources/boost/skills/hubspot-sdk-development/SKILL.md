---
name: hubspot-sdk-development
description: Build and extend the Saloon API SDK package — connector, requests, responses, Laravel integration, and tests.
---

# Saloon API SDK (template) development

## When to use this skill

Use this skill when working in the `laravel-gtm/hubspot-sdk` package: adding endpoints, request/response types, Laravel wiring, or tests. This is a **Saloon 4** HTTP client template, not a specific API — replace the example `ping()` / `ExampleGetRequest` with real routes for your service.

## SDK entry point

Inject `HubspotSdk` or use the static factory:

```php
use LaravelGtm\HubspotSdk\HubspotSdk;

// Via Laravel container (recommended)
$sdk = app(HubspotSdk::class);

// Standalone
$sdk = HubspotSdk::make(
    baseUrl: 'claude',
    token: 'your-token',
);
```

## Connector

`HubspotConnector` resolves the base URL, default headers, optional `HeaderAuthenticator`, timeouts, and rate-limit plugin storage. Register or construct it consistently with your config/env keys (`HUBSPOT_*` before running the init script).

## Requests and responses

- One **request class per endpoint**, under `src/Requests/`, extending `Saloon\Http\Request`.
- **Response DTOs** under `src/Responses/` with typed properties and `fromArray()` where useful.
- Add public methods on `HubspotSdk` that delegate to `$this->connector->send()` and return DTOs.

Example (template):

```php
// src/HubspotSdk.php — ping() sends ExampleGetRequest
public function ping(): Response
{
    return $this->connector->send(new ExampleGetRequest);
}
```

## Laravel

The service provider merges config from `config/hubspot-sdk.php`, binds the connector and SDK as singletons, and publishes the config with tag `hubspot-sdk-config`. After customization, align env names with your chosen prefix.

## Rate limiting

When enabled, the SDK uses Saloon’s rate-limit plugin; in Laravel, the store is typically cache-backed. Tune limits to match your upstream API.

## Testing

Use Saloon fakes — never hit production APIs in tests:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use LaravelGtm\HubspotSdk\Requests\ExampleGetRequest;

$mockClient = new MockClient([
    ExampleGetRequest::class => MockResponse::make(['status' => 'ok']),
]);

$connector->withMockClient($mockClient);
```

The test suite uses Pest + Orchestra Testbench; see `tests/` and `phpunit.xml.dist`.

## Conventions

- PHP 8.4+, `declare(strict_types=1)`.
- PHPStan level 8 (see `phpstan.neon.dist`).
- Format with Laravel Pint via `composer format` / `composer lint`.
- Follow project rules under `.claude/rules/` and `.cursor/rules/` (Saloon, PHPStan, Laravel package).

## Init script

New repos from the GitHub template should run `./init-saloon-sdk.sh` once to replace `laravel-gtm`, `hubspot-sdk`, class names, and env prefixes. That updates this skill path and frontmatter to match the package slug.
