<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\AssociationListResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetCompanyContactAssociationsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $companyId,
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/companies/'.$this->companyId.'/associations/contacts';
    }

    /**
     * @return array<string, string|int>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'limit' => $this->limit,
            'after' => $this->after,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): AssociationListResponse
    {
        /** @var array{results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return AssociationListResponse::fromArray($data);
    }
}
