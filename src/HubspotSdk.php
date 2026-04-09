<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LaravelGtm\HubspotSdk\Requests\GetDealRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealPropertiesRequest;
use LaravelGtm\HubspotSdk\Requests\ListDealsRequest;
use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealPropertiesResponse;
use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
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
}
