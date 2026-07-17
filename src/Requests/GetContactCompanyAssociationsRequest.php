<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\ContactCompanyAssociationsResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class GetContactCompanyAssociationsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $contactId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v4/objects/contacts/'.$this->contactId.'/associations/companies';
    }

    public function createDtoFromResponse(Response $response): ContactCompanyAssociationsResponse
    {
        /** @var array{results: list<array<string, mixed>>, paging?: array<string, mixed>|null} $data */
        $data = $response->json();

        return ContactCompanyAssociationsResponse::fromArray($data);
    }
}
