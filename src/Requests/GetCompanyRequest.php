<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\GetCompanyResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetCompanyRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  list<string>|null  $properties
     * @param  list<string>|null  $propertiesWithHistory
     * @param  list<string>|null  $associations
     */
    public function __construct(
        private readonly string $companyId,
        private readonly ?array $properties = null,
        private readonly ?array $propertiesWithHistory = null,
        private readonly ?array $associations = null,
        private readonly ?bool $archived = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/companies/'.$this->companyId;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'properties' => $this->properties !== null ? implode(',', $this->properties) : null,
            'propertiesWithHistory' => $this->propertiesWithHistory !== null ? implode(',', $this->propertiesWithHistory) : null,
            'associations' => $this->associations !== null ? implode(',', $this->associations) : null,
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): GetCompanyResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return GetCompanyResponse::fromArray($data);
    }
}
