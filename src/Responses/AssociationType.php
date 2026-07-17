<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class AssociationType implements \JsonSerializable
{
    public function __construct(
        public string $category,
        public int $typeId,
        public ?string $label = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'category' => $this->category,
            'typeId' => $this->typeId,
            'label' => $this->label,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            category: (string) ($data['category'] ?? ''),
            typeId: (int) ($data['typeId'] ?? 0),
            label: isset($data['label']) && is_string($data['label']) ? $data['label'] : null,
        );
    }

    /**
     * Whether this is HubSpot's contact→company primary association type
     * (HUBSPOT_DEFINED, typeId 1).
     */
    public function isPrimaryCompany(): bool
    {
        return $this->category === 'HUBSPOT_DEFINED' && $this->typeId === 1;
    }
}
