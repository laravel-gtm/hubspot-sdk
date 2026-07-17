<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\AssociationResult;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class SetPrimaryCompanyAssociationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $contactId,
        private readonly string $companyId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v4/objects/contacts/'.$this->contactId.'/associations/companies/'.$this->companyId;
    }

    /**
     * @return list<array{associationCategory: string, associationTypeId: int}>
     */
    protected function defaultBody(): array
    {
        return [
            [
                'associationCategory' => 'HUBSPOT_DEFINED',
                'associationTypeId' => 1,
            ],
        ];
    }

    public function createDtoFromResponse(Response $response): AssociationResult
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return AssociationResult::fromArray($data);
    }
}
