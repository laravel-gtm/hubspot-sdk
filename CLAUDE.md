# Project instructions

Guidance for agents working in this repository. Detailed rules live under [`.claude/rules/`](.claude/rules/) (Saloon 4, PHPStan level 8, Laravel package conventions — traced from the `luma-sdk` reference package). Cursor loads the same content from [`.cursor/rules/`](.cursor/rules/). Laravel Boost-style guidelines and skills live under [`resources/boost/`](resources/boost/) (see README); `./init-saloon-sdk.sh` updates slugs and renames the skill directory to match the package slug.

## Commands

```bash
composer test                              # Pest
composer test -- tests/Unit/SomeTest.php   # Single file
composer lint                              # Pint (check)
composer format                            # Pint (fix)
composer analyse                           # PHPStan (level 8)
```

## Required checks before finishing work

```bash
composer test
composer analyse
composer lint
```

If `composer lint` fails, run `composer format` and rerun `composer lint`.

## Architecture

This is a **Laravel package**: a Saloon 4 HTTP SDK with an optional Laravel service provider.

```
HubspotSdk → HubspotConnector → Request classes → Response DTOs
```

- **`HubspotConnector`** — Base URL, JSON headers, optional `HeaderAuthenticator` (configurable header name), rate limits, timeouts.
- **`HubspotSdk`** — Public entrypoint; `make()` for standalone use; add methods that send requests and return DTOs.
- **`src/Requests/`** — One class per endpoint, extending `Saloon\Http\Request`.
- **`src/Responses/`** — DTOs with `fromArray()` (or Saloon DTOs) as appropriate.
- **`src/Laravel/HubspotServiceProvider`** — Binds connector and SDK; publishes config (see README for publish tag after the one-time `./init-saloon-sdk.sh`, which deletes itself when it finishes successfully).

## Adding an endpoint

1. Add a `Request` under `src/Requests/`.
2. Add a response type under `src/Responses/` if needed.
3. Expose a method on `HubspotSdk` (or a future `Resources/*` class) that sends the request and returns the DTO.
4. Cover with `MockClient` / `MockResponse` in tests. `tests/Pest.php` enables `Config::preventStrayRequests()`.

## Conventions

- PHP 8.4+, `declare(strict_types=1)` in all PHP files.
- Prefer explicit types; PHPStan level 8 must stay clean.
- This template uses `laravel/pint` from `require-dev` and `vendor/bin/pint` via Composer scripts (see `.claude/rules/laravel-package.md` for broader Laravel package guidance).

After a successful `./init-saloon-sdk.sh` run, class names and namespaces match your choices: Composer `laravel-gtm` / `hubspot-sdk`, a PascalCase class prefix (Sdk/Connector/ServiceProvider appended by the script), and derived root namespace `{VendorPascal}{Prefix}Sdk` (see README). The init script is removed at the end of that run.
