# Saloon API SDK (template)

Boilerplate for Laravel-ready PHP API client packages using [Saloon](https://docs.saloon.dev/) v4: connector, optional rate limiting, Laravel service provider, Pest, PHPStan, and Pint.

This repository is intended to be used as a [GitHub template](https://docs.github.com/en/repositories/creating-and-managing-repositories/creating-a-repository-from-a-template). After you create a new repository from it, customize the package identity and class names with the one-time init script below (it deletes itself after a successful run).

## Requirements

- PHP `^8.4`
- Laravel `^11.0 || ^12.0 || ^13.0` (for the optional Laravel integration)

## AI and editor rules

This template includes the same rule layout as the `luma-sdk` package: [`.claude/rules/`](.claude/rules/) (`saloon.md`, `php-package-phpstan.md`, `laravel-package.md`) for Claude Code, and [`.cursor/rules/`](.cursor/rules/) (`.mdc` mirrors with `globs` front matter) for Cursor. The Laravel rules file notes that **this repo uses `laravel/pint` in `require-dev`** and `composer lint` / `composer format`, which differs from the optional “global Pint only” wording elsewhere in that document.

[Laravel Boost](https://github.com/laravel/boost)-style helpers live under [`resources/boost/`](resources/boost/): [`guidelines/core.blade.php`](resources/boost/guidelines/core.blade.php) and a skill at [`resources/boost/skills/hubspot-sdk-development/SKILL.md`](resources/boost/skills/hubspot-sdk-development/SKILL.md). The init script rewrites vendor/package slugs there and renames the skill folder to `{package-slug}-development`.

[CLAUDE.md](CLAUDE.md) at the repo root summarizes commands, checks, and architecture; run `./init-saloon-sdk.sh` once after creating a new repo so names in that file match your package.

## First-time setup (after “Use this template”)

1. Clone your new repository and install dependencies:

   ```bash
   composer install
   ```

2. Run the initializer at the repo root. It will prompt for:

   - **Composer vendor** — replaces `laravel-gtm` (e.g. `laravel-gtm`).
   - **Package slug** — replaces `hubspot-sdk` everywhere, including the second segment of the Composer name and the config file basename (e.g. `hubspot-sdk` → `laravel-gtm/hubspot-sdk`).
   - **Short class prefix** — PascalCase, **without** `Sdk` on the end; the script adds `Sdk`, `Connector`, and `ServiceProvider` for you (e.g. `Hubspot` → `HubspotSdk`, `HubspotConnector`, `HubspotServiceProvider`).
   - **Env prefix** — replaces `HUBSPOT_` in the published config (e.g. `HUBSPOT_API`).
   - **Default API base URL**.

   The PHP root namespace is derived as `{VendorPascal}{Prefix}Sdk` (e.g. `laravel-gtm` + `Hubspot` → `LaravelGtm\HubspotSdk`). `composer.json` keeps JSON’s doubled backslashes (`\\`) in `autoload` and `extra.laravel.providers`; you do not need to type those.

   ```bash
   chmod +x init-saloon-sdk.sh
   ./init-saloon-sdk.sh
   ```

   Non-interactive (e.g. CI), same values as environment variables:

   ```bash
   export COMPOSER_VENDOR='laravel-gtm'
   export PACKAGE_SLUG='hubspot-sdk'
   export SHORT_PREFIX='Hubspot'
   export ENV_PREFIX='HUBSPOT_API'
   export DEFAULT_BASE_URL='claude'
   ./init-saloon-sdk.sh
   ```

   Preview changes without writing files (`--dry-run` does not delete the script):

   ```bash
   ./init-saloon-sdk.sh --dry-run
   ```

   After a **successful** full run (Composer install, tests, PHPStan, Pint), `init-saloon-sdk.sh` removes itself so it cannot be applied twice by mistake.

3. Update README badges and repository URLs (Packagist, GitHub Actions) for your package.

4. Replace the example `ExampleGetRequest` / `ping()` flow with real endpoints, DTOs, and resources for your API.

5. In GitHub: **Settings → General → Template repository** — enable only on the canonical template repo, not on forks meant for production.

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

### Example call

The template exposes a `ping()` method wired to `GET /v1/ping` — replace this with real API methods.

## Development

```bash
composer test        # Pest
composer analyse     # PHPStan
composer lint        # Pint (check)
composer format      # Pint (fix)
```

## License

MIT. See [LICENSE](LICENSE).
