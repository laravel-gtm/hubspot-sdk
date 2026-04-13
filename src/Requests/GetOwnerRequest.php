<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\Owner;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetOwnerRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $ownerId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/owners/'.$this->ownerId;
    }

    public function createDtoFromResponse(Response $response): Owner
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return Owner::fromArray($data);
    }
}
