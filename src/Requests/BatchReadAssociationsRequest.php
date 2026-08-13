<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\BatchAssociationsResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

/**
 * Read associations from up to 100 objects to a target object type in one
 * call (v4 Associations API). Object types may be names (`contacts`) or
 * custom object type IDs (`2-61391055`).
 */
class BatchReadAssociationsRequest extends Request implements HasBody
{
    use HasJsonBody;

    public const MaxInputs = 100;

    protected Method $method = Method::POST;

    /**
     * @param  list<string>  $objectIds
     */
    public function __construct(
        private readonly string $fromObjectType,
        private readonly string $toObjectType,
        private readonly array $objectIds,
    ) {
        if (count($this->objectIds) > self::MaxInputs) {
            throw new \InvalidArgumentException(
                sprintf('HubSpot batch association reads accept at most %d IDs per call, %d given.', self::MaxInputs, count($this->objectIds)),
            );
        }
    }

    public function resolveEndpoint(): string
    {
        return "/crm/v4/associations/{$this->fromObjectType}/{$this->toObjectType}/batch/read";
    }

    /**
     * @return array{inputs: list<array{id: string}>}
     */
    protected function defaultBody(): array
    {
        return [
            'inputs' => array_map(
                static fn (string $id): array => ['id' => $id],
                $this->objectIds,
            ),
        ];
    }

    public function createDtoFromResponse(Response $response): BatchAssociationsResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return BatchAssociationsResponse::fromArray($data);
    }
}
