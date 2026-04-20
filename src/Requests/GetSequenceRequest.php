<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\Sequence;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetSequenceRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $sequenceId,
        private readonly string $userId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/automation/sequences/2026-03/'.$this->sequenceId;
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }

    public function createDtoFromResponse(Response $response): Sequence
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return Sequence::fromArray($data);
    }
}
