<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ListDealPropertiesResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListDealPropertiesRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  'highly_sensitive'|'non_sensitive'|'sensitive'|null  $dataSensitivity
     */
    public function __construct(
        private readonly ?bool $archived = null,
        private readonly ?string $dataSensitivity = null,
        private readonly ?string $locale = null,
        private readonly ?string $properties = null,
    ) {}

    /**
     * CRM Properties v3 — list all properties for the deal object.
     *
     * Object type `deal` matches `GET /crm/v3/properties/deal/{propertyName}`.
     *
     * @see https://developers.hubspot.com/docs/api-reference/crm-properties-v3/core/get-crm-v3-properties-objectType
     */
    public function resolveEndpoint(): string
    {
        return '/crm/v3/properties/deal';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
            'dataSensitivity' => $this->dataSensitivity,
            'locale' => $this->locale,
            'properties' => $this->properties,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListDealPropertiesResponse
    {
        /** @var array{results: list<array<string, mixed>>} $data */
        $data = $response->json();

        return ListDealPropertiesResponse::fromArray($data);
    }
}
