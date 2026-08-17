# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- `HubspotConnector` now sleeps (delays + retries) instead of throwing `RateLimitReachedException` when the burst limit or HubSpot's own 429 response is hit. The connector is typically bound as a container singleton over a shared, cache-backed rate limit store, so one caller's burst was tripping a hard failure for every other concurrent caller sharing that store. The daily limit intentionally still throws rather than sleeps, since blocking a queue worker for up to 24 hours would be worse than failing fast.

## [0.0.16] - 2026-08-13

### Added
- `searchObjects()` endpoint for searching any CRM object type — including custom objects addressed by object type ID (e.g. `2-61391055`) — with filter groups, sorts, and pagination
- `listObjectProperties()` endpoint for enumerating property definitions of any CRM object type, with `includeHidden` support for calculated/admin-hidden properties
- `batchReadObjects()` endpoint for reading up to 100 records of any CRM object type by ID in one call, with an `archived` flag and `resultIds()` for deletion diffing
- `batchReadAssociations()` endpoint for the v4 associations batch read (up to 100 `from` IDs per call), with `toIdsByFromId()` for join maps
- `CrmObject`, `SearchObjectsResponse`, `ListObjectPropertiesResponse`, `BatchObjectsResponse`, `BatchAssociationsResponse`, `AssociationBatchResult`, and `AssociationTarget` DTOs

## [0.0.15] - 2026-07-17

### Added
- `setPrimaryCompanyAssociation()`, `getContactCompanyAssociations()`, and `demotePrimaryCompanyAssociation()` endpoints for managing a contact's primary company (v4 Associations API)
- `AssociationType`, `ContactCompanyAssociation`, and `ContactCompanyAssociationsResponse` DTOs

## [0.0.14] - 2026-07-16

### Added
- `associateContactWithCompany()` endpoint using HubSpot's default (unlabeled) v4 association
- `AssociationResult` DTO

### Changed
- Create requests are no longer retried, so a transient failure can't produce duplicate records

## [0.0.13] - 2026-07-13

### Added
- `createContact()` endpoint for creating a new contact (POST)
- `createCompany()` endpoint for creating a new company (POST)
- `updateCompany()` endpoint for updating company properties (PATCH)
- `onUnauthorized()` hook on `HubspotConnector` (and `HubspotSdk`) invoked with the response whenever a request fails with a 401, before the exception propagates — useful for alerting on expired or revoked OAuth tokens

## [0.0.12] - 2026-07-13

### Changed
- Dependency bumps

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

[Unreleased]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.16...HEAD
[0.0.16]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.15...v0.0.16
[0.0.15]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.14...v0.0.15
[0.0.14]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.13...v0.0.14
[0.0.13]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.12...v0.0.13
[0.0.12]: https://github.com/laravel-gtm/hubspot-sdk/compare/v0.0.11...v0.0.12
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
