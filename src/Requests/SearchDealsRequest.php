<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\SearchDealsResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class SearchDealsRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  list<array<string, mixed>>  $filterGroups
     * @param  list<string>|null  $properties
     * @param  list<array<string, string>>|null  $sorts
     */
    public function __construct(
        private readonly array $filterGroups = [],
        private readonly ?array $properties = null,
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
        private readonly ?array $sorts = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/deals/search';
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

    public function createDtoFromResponse(Response $response): SearchDealsResponse
    {
        /** @var array{total: int, results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return SearchDealsResponse::fromArray($data);
    }
}
