<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ListObjectPropertiesResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Enumerate property definitions for any CRM object type, including custom
 * objects addressed by object type ID (e.g. `2-61391055`).
 *
 * Pass `includeHidden: true` to include calculated and admin-hidden
 * properties, which are excluded by default. Sensitive-flagged properties
 * additionally require `dataSensitivity` plus the matching read scope.
 */
class ListObjectPropertiesRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  'highly_sensitive'|'non_sensitive'|'sensitive'|null  $dataSensitivity
     */
    public function __construct(
        private readonly string $objectTypeId,
        private readonly ?bool $archived = null,
        private readonly ?bool $includeHidden = null,
        private readonly ?string $dataSensitivity = null,
        private readonly ?string $locale = null,
        private readonly ?string $properties = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/crm/v3/properties/{$this->objectTypeId}";
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
            'includeHidden' => $this->includeHidden !== null ? ($this->includeHidden ? 'true' : 'false') : null,
            'dataSensitivity' => $this->dataSensitivity,
            'locale' => $this->locale,
            'properties' => $this->properties,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListObjectPropertiesResponse
    {
        /** @var array{results: list<array<string, mixed>>} $data */
        $data = $response->json();

        return ListObjectPropertiesResponse::fromArray($data);
    }
}
