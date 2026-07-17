<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class ContactCompanyAssociation implements \JsonSerializable
{
    /**
     * @param  list<AssociationType>  $associationTypes
     */
    public function __construct(
        public string $companyId,
        public array $associationTypes,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'companyId' => $this->companyId,
            'associationTypes' => $this->associationTypes,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $types */
        $types = is_array($data['associationTypes'] ?? null) ? $data['associationTypes'] : [];

        return new self(
            companyId: (string) ($data['toObjectId'] ?? ''),
            associationTypes: array_map(
                static fn (array $type): AssociationType => AssociationType::fromArray($type),
                $types,
            ),
        );
    }

    public function isPrimary(): bool
    {
        foreach ($this->associationTypes as $type) {
            if ($type->isPrimaryCompany()) {
                return true;
            }
        }

        return false;
    }
}
