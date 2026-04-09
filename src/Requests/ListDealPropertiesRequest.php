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

    public function resolveEndpoint(): string
    {
        return '/crm/properties/2026-03/deals';
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
