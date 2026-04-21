<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\GetContactResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class UpdateContactRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /**
     * @param  array<string, string|int|float|bool|null>  $properties
     */
    public function __construct(
        private readonly string $contactId,
        private readonly array $properties,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v3/objects/contacts/'.$this->contactId;
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

    public function createDtoFromResponse(Response $response): GetContactResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return GetContactResponse::fromArray($data);
    }
}
