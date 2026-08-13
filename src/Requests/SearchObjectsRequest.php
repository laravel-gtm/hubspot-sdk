<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\SearchObjectsResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Search any CRM object type, including custom objects addressed by
 * object type ID (e.g. `2-61391055`).
 *
 * Note: the Search API caps at 10,000 results per query — paging past
 * `after=10000` returns a 400. Sort by `hs_object_id` ascending and re-seed
 * with an `hs_object_id GT {last}` filter to page past the cap.
 */
class SearchObjectsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  list<array<string, mixed>>  $filterGroups
     * @param  list<string>|null  $properties
     * @param  list<array<string, string>>|null  $sorts
     */
    public function __construct(
        private readonly string $objectTypeId,
        private readonly array $filterGroups = [],
        private readonly ?array $properties = null,
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
        private readonly ?array $sorts = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/crm/v3/objects/{$this->objectTypeId}/search";
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return array_filter([
            'filterGroups' => $this->filterGroups,
            'properties' => $this->properties,
            'limit' => $this->limit,
            'after' => $this->after,
            'sorts' => $this->sorts,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): SearchObjectsResponse
    {
        /** @var array{total: int, results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return SearchObjectsResponse::fromArray($data);
    }
}
