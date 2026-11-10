<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\GetDealResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateDealRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /**
     * @param  array<string, string|int|float|bool|null>  $properties
     */
    public function __construct(
        private readonly string $dealId,
        private readonly array $properties,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/deals/'.$this->dealId;
    }

    /**
     * @return array<string, array<string, string|int|float|bool|null>>
     */
    protected function defaultBody(): array
    {
        return [
            'properties' => $this->properties,
        ];
    }

    public function createDtoFromResponse(Response $response): GetDealResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return GetDealResponse::fromArray($data);
    }
}
