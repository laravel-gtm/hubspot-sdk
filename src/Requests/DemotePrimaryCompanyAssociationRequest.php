<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class DemotePrimaryCompanyAssociationRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $contactId,
        private readonly string $companyId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/crm/v4/associations/contacts/companies/batch/labels/archive';
    }

    /**
     * @return array{inputs: list<array{from: array{id: string}, to: array{id: string}, types: list<array{associationCategory: string, associationTypeId: int}>}>}
     */
    protected function defaultBody(): array
    {
        return [
            'inputs' => [
                [
                    'from' => ['id' => $this->contactId],
                    'to' => ['id' => $this->companyId],
                    'types' => [
                        [
                            'associationCategory' => 'HUBSPOT_DEFINED',
                            'associationTypeId' => 1,
                        ],
                    ],
                ],
            ],
        ];
    }
}
