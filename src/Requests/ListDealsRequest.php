<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ListDealsResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListDealsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function __construct(
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
        private readonly ?array $properties = null,
        private readonly ?array $propertiesWithHistory = null,
        private readonly ?array $associations = null,
        private readonly ?bool $archived = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/deals';
    }

    /**
     * @return array<string, string|int>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'after' => $this->after,
            'properties' => $this->properties !== null ? implode(',', $this->properties) : null,
            'propertiesWithHistory' => $this->propertiesWithHistory !== null ? implode(',', $this->propertiesWithHistory) : null,
            'associations' => $this->associations !== null ? implode(',', $this->associations) : null,
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListDealsResponse
    {
        /** @var array{results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return ListDealsResponse::fromArray($data);
    }
}
