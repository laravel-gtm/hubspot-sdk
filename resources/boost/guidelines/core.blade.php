## Saloon API SDK (`laravel-gtm/hubspot-sdk`)

This package is a **Laravel-ready PHP API client** built on [Saloon](https://docs.saloon.dev/) v4: a connector, optional rate limiting, typed request classes, and response DTOs. Replace the template `ExampleGetRequest` / `ping()` flow with real endpoints for your API.

### Setup

Add credentials to `.env` using your package’s env prefix (before init: `HUBSPOT_*`):

```
HUBSPOT_TOKEN=your-token-here
HUBSPOT_BASE_URL=claude
```

Publish the config when you need to customize defaults:

```bash
php artisan vendor:publish --tag=hubspot-sdk-config
```

### Usage

- **`HubspotSdk`** is the main entry point. Inject it via the Laravel container or use `HubspotSdk::make()` for standalone use.
- **`HubspotConnector`** holds base URL, auth headers, timeouts, and rate-limit storage.
- Add **request classes** under `src/Requests/` (one per endpoint) and **response DTOs** under `src/Responses/`.
- Expose API operations as methods on `HubspotSdk` (or future `Resources/*` classes).

### Request pattern

Use Saloon request classes extending `Saloon\Http\Request`. Pass parameters via the constructor or resolver methods as appropriate for your API.

@verbatim
<code-snippet name="Calling the template ping endpoint" lang="php">
use LaravelGtm\HubspotSdk\HubspotSdk;

$sdk = app(HubspotSdk::class);

$sdk->ping();
</code-snippet>
@endverbatim

### Testing

Use Saloon’s `MockClient` and `MockResponse`. Never make real API calls in automated tests:

@verbatim
<code-snippet name="Mocking HTTP calls" lang="php">
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use LaravelGtm\HubspotSdk\Requests\ExampleGetRequest;

$mockClient = new MockClient([
    ExampleGetRequest::class => MockResponse::make(['ok' => true]),
]);

$connector->withMockClient($mockClient);
</code-snippet>
@endverbatim

### Important notes

- In Laravel, optional rate limiting uses the application cache when configured.
- Prefer typed response DTOs with `fromArray()` (or Saloon DTOs) over raw arrays.
- Run `vendor/bin/pint` and `phpstan` before shipping changes; see the package `composer.json` scripts.
