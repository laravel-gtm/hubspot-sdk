<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LaravelGtm\HubspotSdk\Requests\GetCompanyContactAssociationsRequest;
use LaravelGtm\HubspotSdk\Requests\GetCompanyRequest;
use LaravelGtm\HubspotSdk\Requests\GetContactRequest;
use LaravelGtm\HubspotSdk\Requests\GetDealRequest;
use LaravelGtm\HubspotSdk\Requests\GetOwnerRequest;
use LaravelGtm\HubspotSdk\Requests\ListContactPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListContactsRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;
use LaravelGtm\HubspotSdk\Requests\ListOwnersRequest;
use LaravelGtm\HubspotSdk\Requests\SearchCompaniesRequest;
use LaravelGtm\HubspotSdk\Requests\SearchContactsRequest;
use LaravelGtm\HubspotSdk\Requests\SearchDealsRequest;
use LaravelGtm\HubspotSdk\Responses\AssociationListResponse;
use LaravelGtm\HubspotSdk\Responses\GetCompanyResponse;
use LaravelGtm\HubspotSdk\Responses\GetContactResponse;
use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use LaravelGtm\HubspotSdk\Responses\ListContactPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListContactsResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use LaravelGtm\HubspotSdk\Responses\ListOwnersResponse;
use LaravelGtm\HubspotSdk\Responses\Owner;
use LaravelGtm\HubspotSdk\Responses\SearchCompaniesResponse;
use LaravelGtm\HubspotSdk\Responses\SearchContactsResponse;
use LaravelGtm\HubspotSdk\Responses\SearchDealsResponse;
use Saloon\Http\Auth\TokenAuthenticator;

class HubspotSdk
{
    public function __construct(
        private readonly HubspotConnector $connector,
        private readonly string $userTokenColumn = 'hubspot_access_token',
    ) {}

    public static function make(?string $baseUrl = null, ?string $token = null): self
    {
        return new self(new HubspotConnector($baseUrl, $token));
    }

    /**
     * Create a new SDK instance authenticated as the given user.
     *
     * Reads the OAuth access token from the configured column on the user model.
     */
    public function forUser(Authenticatable $user): self
    {
        if (! $user instanceof Model) {
            throw new \InvalidArgumentException(
                'The user must be an Eloquent Model instance to retrieve the OAuth token.',
            );
        }

        $column = $this->userTokenColumn;

        /** @var mixed $token */
        $token = $user->getAttribute($column);

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException(
                "The user does not have a HubSpot OAuth token in the '{$column}' column.",
            );
        }

        $connector = clone $this->connector;
        $connector->authenticate(new TokenAuthenticator($token));

        return new self($connector, $this->userTokenColumn);
    }

    /**
     * Get a single contact by ID, optionally enriched with associations.
     *
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function getContact(
        string $contactId,
        ?array $properties = null,
        ?array $propertiesWithHistory = null,
        ?array $associations = null,
        ?bool $archived = null,
    ): GetContactResponse {
        /** @var GetContactResponse */
        return $this->connector
            ->send(new GetContactRequest($contactId, $properties, $propertiesWithHistory, $associations, $archived))
            ->dtoOrFail();
    }

    /**
     * List contacts from the HubSpot CRM.
     *
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function listContacts(
        ?int $limit = null,
        ?string $after = null,
        ?array $properties = null,
        ?array $propertiesWithHistory = null,
        ?array $associations = null,
        ?bool $archived = null,
    ): ListContactsResponse {
        /** @var ListContactsResponse */
        return $this->connector
            ->send(new ListContactsRequest($limit, $after, $properties, $propertiesWithHistory, $associations, $archived))
            ->dtoOrFail();
    }

    /**
     * List contact property definitions (CRM Properties API).
     *
     * @param  'highly_sensitive'|'non_sensitive'|'sensitive'|null  $dataSensitivity
     */
    public function listContactProperties(
        ?bool $archived = null,
        ?string $dataSensitivity = null,
        ?string $locale = null,
        ?string $properties = null,
    ): ListContactPropertiesResponse {
        /** @var ListContactPropertiesResponse */
        return $this->connector
            ->send(new ListContactPropertiesRequest($archived, $dataSensitivity, $locale, $properties))
            ->dtoOrFail();
    }

    /**
     * Get a single deal by ID, optionally enriched with associations.
     *
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function getDeal(
        string $dealId,
        ?array $properties = null,
        ?array $propertiesWithHistory = null,
        ?array $associations = null,
        ?bool $archived = null,
    ): GetDealResponse {
        /** @var GetDealResponse */
        return $this->connector
            ->send(new GetDealRequest($dealId, $properties, $propertiesWithHistory, $associations, $archived))
            ->dtoOrFail();
    }

    /**
     * Get a single company by ID, optionally enriched with associations.
     *
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function getCompany(
        string $companyId,
        ?array $properties = null,
        ?array $propertiesWithHistory = null,
        ?array $associations = null,
        ?bool $archived = null,
    ): GetCompanyResponse {
        /** @var GetCompanyResponse */
        return $this->connector
            ->send(new GetCompanyRequest($companyId, $properties, $propertiesWithHistory, $associations, $archived))
            ->dtoOrFail();
    }

    /**
     * List deals from the HubSpot CRM.
     *
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function listDeals(
        ?int $limit = null,
        ?string $after = null,
        ?array $properties = null,
        ?array $propertiesWithHistory = null,
        ?array $associations = null,
        ?bool $archived = null,
    ): ListDealsResponse {
        /** @var ListDealsResponse */
        return $this->connector
            ->send(new ListDealsRequest($limit, $after, $properties, $propertiesWithHistory, $associations, $archived))
            ->dtoOrFail();
    }

    /**
     * List deal property definitions (CRM Properties API).
     *
     * @param  'highly_sensitive'|'non_sensitive'|'sensitive'|null  $dataSensitivity
     */
    public function listDealProperties(
        ?bool $archived = null,
        ?string $dataSensitivity = null,
        ?string $locale = null,
        ?string $properties = null,
    ): ListDealPropertiesResponse {
        /** @var ListDealPropertiesResponse */
        return $this->connector
            ->send(new ListDealPropertiesRequest($archived, $dataSensitivity, $locale, $properties))
            ->dtoOrFail();
    }

    /**
     * Search companies using filter groups (CRM Search API).
     *
     * @param  list<array<string, mixed>>  $filterGroups
     * @param  list<string>|null  $properties
     * @param  list<array<string, string>>|null  $sorts
     */
    public function searchCompanies(
        array $filterGroups = [],
        ?array $properties = null,
        ?int $limit = null,
        ?string $after = null,
        ?array $sorts = null,
    ): SearchCompaniesResponse {
        /** @var SearchCompaniesResponse */
        return $this->connector
            ->send(new SearchCompaniesRequest($filterGroups, $properties, $limit, $after, $sorts))
            ->dtoOrFail();
    }

    /**
     * Search deals using filter groups (CRM Search API).
     *
     * @param  list<array<string, mixed>>  $filterGroups
     * @param  list<string>|null  $properties
     * @param  list<array<string, string>>|null  $sorts
     */
    public function searchDeals(
        array $filterGroups = [],
        ?array $properties = null,
        ?int $limit = null,
        ?string $after = null,
        ?array $sorts = null,
    ): SearchDealsResponse {
        /** @var SearchDealsResponse */
        return $this->connector
            ->send(new SearchDealsRequest($filterGroups, $properties, $limit, $after, $sorts))
            ->dtoOrFail();
    }

    /**
     * Search contacts using filter groups (CRM Search API).
     *
     * @param  list<array<string, mixed>>  $filterGroups
     * @param  list<string>|null  $properties
     * @param  list<array<string, string>>|null  $sorts
     */
    public function searchContacts(
        array $filterGroups = [],
        ?array $properties = null,
        ?int $limit = null,
        ?string $after = null,
        ?array $sorts = null,
    ): SearchContactsResponse {
        /** @var SearchContactsResponse */
        return $this->connector
            ->send(new SearchContactsRequest($filterGroups, $properties, $limit, $after, $sorts))
            ->dtoOrFail();
    }

    /**
     * Get contacts associated with a company.
     */
    public function getCompanyContactAssociations(
        string $companyId,
        ?int $limit = null,
        ?string $after = null,
    ): AssociationListResponse {
        /** @var AssociationListResponse */
        return $this->connector
            ->send(new GetCompanyContactAssociationsRequest($companyId, $limit, $after))
            ->dtoOrFail();
    }

    /**
     * Get a single owner by ID.
     */
    public function getOwner(string $ownerId): Owner
    {
        /** @var Owner */
        return $this->connector
            ->send(new GetOwnerRequest($ownerId))
            ->dtoOrFail();
    }

    /**
     * List owners, optionally filtered by email.
     */
    public function listOwners(
        ?string $email = null,
        ?int $limit = null,
        ?string $after = null,
        ?bool $archived = null,
    ): ListOwnersResponse {
        /** @var ListOwnersResponse */
        return $this->connector
            ->send(new ListOwnersRequest($email, $limit, $after, $archived))
            ->dtoOrFail();
    }
}
