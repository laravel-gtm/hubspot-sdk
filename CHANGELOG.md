# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `getDeal()` endpoint for fetching a single deal by ID with optional associations (contacts, companies, line_items, etc.)
- `GetDealResponse` and `Association` response DTOs

## [0.0.4] - 2026-04-09

### Fixed
- Deal properties endpoint now uses correct CRM v3 API path (`/crm/v3/properties/deal`)

## [0.0.3] - 2026-04-09

### Added
- `listDealProperties()` endpoint for fetching deal property definitions
- `CrmProperty` and `CrmPropertyOption` response DTOs
- `ListDealPropertiesResponse` DTO

## [0.0.2] - 2026-04-08

### Changed
- Response DTOs (`Deal`, `ListDealsResponse`, `Paging`) now implement `JsonSerializable` for direct use in Laravel route returns

## [0.0.1] - 2026-04-08

### Added
- `HubspotConnector` with token auth, rate limiting (burst + daily), and configurable timeouts
- `HubspotSdk` public entrypoint with `make()` for standalone use and `forUser()` for OAuth
- `listDeals()` endpoint with pagination, property selection, and association support
- `Deal`, `ListDealsResponse`, and `Paging` response DTOs
- Laravel service provider with config publishing (`hubspot-sdk-config`)

[Unreleased]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.4...HEAD
[0.0.4]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.3...v0.0.4
[0.0.3]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.2...v0.0.3
[0.0.2]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.1...v0.0.2
[0.0.1]: https://github.com/laravel-gtm/hubspot-sdk/releases/tag/v0.0.1
