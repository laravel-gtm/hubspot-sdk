<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ListSequencesResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class ListSequencesRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $userId,
        private readonly ?int $limit = null,
        private readonly ?string $after = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/automation/sequences/2026-03';
    }

    /**
     * @return array<string, string|int>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'userId' => $this->userId,
            'limit' => $this->limit,
            'after' => $this->after,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): ListSequencesResponse
    {
        /** @var array{total?: int, results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return ListSequencesResponse::fromArray($data);
    }
}
