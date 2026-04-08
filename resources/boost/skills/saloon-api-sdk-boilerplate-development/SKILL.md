---
name: saloon-api-sdk-boilerplate-development
description: Build and extend the Saloon API SDK package — connector, requests, responses, Laravel integration, and tests.
---

# Saloon API SDK (template) development

## When to use this skill

Use this skill when working in the `your-vendor/saloon-api-sdk-boilerplate` package: adding endpoints, request/response types, Laravel wiring, or tests. This is a **Saloon 4** HTTP client template, not a specific API — replace the example `ping()` / `ExampleGetRequest` with real routes for your service.

## SDK entry point

Inject `SaloonApiSdk` or use the static factory:

```php
use YourVendor\SaloonApiSdk\SaloonApiSdk;

// Via Laravel container (recommended)
$sdk = app(SaloonApiSdk::class);

// Standalone
$sdk = SaloonApiSdk::make(
    baseUrl: 'https://api.example.com',
    token: 'your-token',
);
```

## Connector

`SaloonConnector` resolves the base URL, default headers, optional `HeaderAuthenticator`, timeouts, and rate-limit plugin storage. Register or construct it consistently with your config/env keys (`SALOON_API_SDK_*` before running the init script).

## Requests and responses

- One **request class per endpoint**, under `src/Requests/`, extending `Saloon\Http\Request`.
- **Response DTOs** under `src/Responses/` with typed properties and `fromArray()` where useful.
- Add public methods on `SaloonApiSdk` that delegate to `$this->connector->send()` and return DTOs.

Example (template):

```php
// src/SaloonApiSdk.php — ping() sends ExampleGetRequest
public function ping(): Response
{
    return $this->connector->send(new ExampleGetRequest);
}
```

## Laravel

The service provider merges config from `config/saloon-api-sdk-boilerplate.php`, binds the connector and SDK as singletons, and publishes the config with tag `saloon-api-sdk-boilerplate-config`. After customization, align env names with your chosen prefix.

## Rate limiting

When enabled, the SDK uses Saloon’s rate-limit plugin; in Laravel, the store is typically cache-backed. Tune limits to match your upstream API.

## Testing

Use Saloon fakes — never hit production APIs in tests:

```php
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use YourVendor\SaloonApiSdk\Requests\ExampleGetRequest;

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

New repos from the GitHub template should run `./init-saloon-sdk.sh` once to replace `your-vendor`, `saloon-api-sdk-boilerplate`, class names, and env prefixes. That updates this skill path and frontmatter to match the package slug.
