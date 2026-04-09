<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class CrmPropertyOption implements \JsonSerializable
{
    public function __construct(
        public bool $hidden,
        public string $label,
        public string $value,
        public ?string $description = null,
        public ?int $displayOrder = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'hidden' => $this->hidden,
            'label' => $this->label,
            'value' => $this->value,
            'description' => $this->description,
            'displayOrder' => $this->displayOrder,
        ], static fn (mixed $v): bool => $v !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            hidden: (bool) ($data['hidden'] ?? false),
            label: (string) $data['label'],
            value: (string) $data['value'],
            description: isset($data['description']) ? (string) $data['description'] : null,
            displayOrder: isset($data['displayOrder']) ? (int) $data['displayOrder'] : null,
        );
    }
}
