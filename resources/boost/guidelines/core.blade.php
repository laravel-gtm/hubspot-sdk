## Saloon API SDK (`your-vendor/saloon-api-sdk-boilerplate`)

This package is a **Laravel-ready PHP API client** built on [Saloon](https://docs.saloon.dev/) v4: a connector, optional rate limiting, typed request classes, and response DTOs. Replace the template `ExampleGetRequest` / `ping()` flow with real endpoints for your API.

### Setup

Add credentials to `.env` using your package’s env prefix (before init: `SALOON_API_SDK_*`):

```
SALOON_API_SDK_TOKEN=your-token-here
SALOON_API_SDK_BASE_URL=https://api.example.com
```

Publish the config when you need to customize defaults:

```bash
php artisan vendor:publish --tag=saloon-api-sdk-boilerplate-config
```

### Usage

- **`SaloonApiSdk`** is the main entry point. Inject it via the Laravel container or use `SaloonApiSdk::make()` for standalone use.
- **`SaloonConnector`** holds base URL, auth headers, timeouts, and rate-limit storage.
- Add **request classes** under `src/Requests/` (one per endpoint) and **response DTOs** under `src/Responses/`.
- Expose API operations as methods on `SaloonApiSdk` (or future `Resources/*` classes).

### Request pattern

Use Saloon request classes extending `Saloon\Http\Request`. Pass parameters via the constructor or resolver methods as appropriate for your API.

@verbatim
<code-snippet name="Calling the template ping endpoint" lang="php">
use YourVendor\SaloonApiSdk\SaloonApiSdk;

$sdk = app(SaloonApiSdk::class);

$sdk->ping();
</code-snippet>
@endverbatim

### Testing

Use Saloon’s `MockClient` and `MockResponse`. Never make real API calls in automated tests:

@verbatim
<code-snippet name="Mocking HTTP calls" lang="php">
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use YourVendor\SaloonApiSdk\Requests\ExampleGetRequest;

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
