<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\BatchObjectsResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Read up to 100 records of any CRM object type by ID in one call. IDs the
 * portal doesn't know (deleted, merged away) are silently omitted from the
 * results — diff `resultIds()` against the requested IDs to detect them.
 *
 * Pass `archived: true` to read archived records instead of active ones.
 */
class BatchReadObjectsRequest extends Request implements HasBody
{
    use HasJsonBody;

    public const MaxInputs = 100;

    protected Method $method = Method::POST;

    /**
     * @param  list<string>  $ids
     * @param  list<string>|null  $properties
     */
    public function __construct(
        private readonly string $objectTypeId,
        private readonly array $ids,
        private readonly ?array $properties = null,
        private readonly ?bool $archived = null,
    ) {
        if (count($this->ids) > self::MaxInputs) {
            throw new \InvalidArgumentException(
                sprintf('HubSpot batch object reads accept at most %d IDs per call, %d given.', self::MaxInputs, count($this->ids)),
            );
        }
    }

    public function resolveEndpoint(): string
    {
        return "/crm/v3/objects/{$this->objectTypeId}/batch/read";
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return array_filter([
            'archived' => $this->archived !== null ? ($this->archived ? 'true' : 'false') : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultBody(): array
    {
        return array_filter([
            'inputs' => array_map(
                static fn (string $id): array => ['id' => $id],
                $this->ids,
            ),
            'properties' => $this->properties,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function createDtoFromResponse(Response $response): BatchObjectsResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return BatchObjectsResponse::fromArray($data);
    }
}
