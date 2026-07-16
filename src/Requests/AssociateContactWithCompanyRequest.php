<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\AssociationResult;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;

class AssociateContactWithCompanyRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $contactId,
        private readonly string $companyId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v4/objects/contacts/'.$this->contactId.'/associations/default/companies/'.$this->companyId;
    }

    public function createDtoFromResponse(Response $response): AssociationResult
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return AssociationResult::fromArray($data);
    }
}
