# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `createContact()` endpoint for creating a new contact (POST)
- `createCompany()` endpoint for creating a new company (POST)
- `updateCompany()` endpoint for updating company properties (PATCH)
- `onUnauthorized()` hook on `HubspotConnector` (and `HubspotSdk`) invoked with the response whenever a request fails with a 401, before the exception propagates — useful for alerting on expired or revoked OAuth tokens

## [0.0.11] - 2026-04-21

### Added
- `updateContact()` endpoint for updating contact properties (PATCH), returning the updated contact as a `GetContactResponse`

## [0.0.10] - 2026-04-20

### Added
- `listSequences()` endpoint for listing sales sequences visible to a given user (paginated)
- `getSequence()` endpoint for fetching a single sequence including its steps and `delayMillis`
- `enrollContactInSequence()` endpoint for enrolling a contact in a sequence
- `Sequence`, `SequenceStep`, `ListSequencesResponse`, and `SequenceEnrollmentResponse` DTOs

## [0.0.9] - 2026-04-13

### Added
- `getCompany()` endpoint for fetching a single company by ID with optional associations
- `searchDeals()` and `searchContacts()` endpoints using the CRM Search API (filter groups, sorts, pagination)
- `GetCompanyResponse`, `SearchDealsResponse`, and `SearchContactsResponse` DTOs

## [0.0.8] - 2026-04-13

### Changed
- Added retry with exponential backoff (3 tries) on `HubspotConnector`
- Lowered the default burst rate limit from its prior value to 150 requests per 10 seconds

## [0.0.7] - 2026-04-13

### Added
- `getCompanyContactAssociations()` endpoint for fetching contacts associated with a company
- `getOwner()` and `listOwners()` endpoints for the HubSpot Owners API
- `searchCompanies()` endpoint using the CRM Search API
- `Company`, `Owner`, `AssociationListResponse`, `ListOwnersResponse`, and `SearchCompaniesResponse` DTOs

## [0.0.6] - 2026-04-09

### Added
- `getContact()` endpoint for fetching a single contact by ID with optional associations
- `listContacts()` endpoint for listing contacts with pagination, property selection, and associations
- `listContactProperties()` endpoint for fetching contact property definitions
- `Contact`, `GetContactResponse`, `ListContactsResponse`, and `ListContactPropertiesResponse` DTOs

## [0.0.5] - 2026-04-09

### Added
- `getDeal()` endpoint for fetching a single deal by ID with optional associations
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

[Unreleased]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.11...HEAD
[0.0.11]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.10...v0.0.11
[0.0.10]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.9...v0.0.10
[0.0.9]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.8...v0.0.9
[0.0.8]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.7...v0.0.8
[0.0.7]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.6...v0.0.7
[0.0.6]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.5...v0.0.6
[0.0.5]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.4...v0.0.5
[0.0.4]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.3...v0.0.4
[0.0.3]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.2...v0.0.3
[0.0.2]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.1...v0.0.2
[0.0.1]: https://github.com/laravel-gtm/hubspot-sdk/releases/tag/v0.0.1
