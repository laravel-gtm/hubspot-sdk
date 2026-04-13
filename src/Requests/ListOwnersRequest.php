<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ListOwnersResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListOwnersRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ?string $email = null,
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
        private readonly ?bool $archived = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/owners';
    }

    /**
     * @return array<string, string|int>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'email' => $this->email,
            'limit' => $this->limit,
            'after' => $this->after,
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListOwnersResponse
    {
        /** @var array{results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return ListOwnersResponse::fromArray($data);
    }
}
