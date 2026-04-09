<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class CrmProperty implements \JsonSerializable
{
    /**
     * @param  list<CrmPropertyOption>  $options
     */
    public function __construct(
        public string $description,
        public string $fieldType,
        public string $groupName,
        public string $label,
        public string $name,
        public array $options,
        public string $type,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'fieldType' => $this->fieldType,
            'groupName' => $this->groupName,
            'label' => $this->label,
            'name' => $this->name,
            'options' => $this->options,
            'type' => $this->type,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $optionsRaw */
        $optionsRaw = isset($data['options']) && is_array($data['options'])
            ? $data['options']
            : [];

        return new self(
            description: (string) ($data['description'] ?? ''),
            fieldType: (string) ($data['fieldType'] ?? ''),
            groupName: (string) ($data['groupName'] ?? ''),
            label: (string) ($data['label'] ?? ''),
            name: (string) ($data['name'] ?? ''),
            options: array_map(
                static fn (array $item): CrmPropertyOption => CrmPropertyOption::fromArray($item),
                $optionsRaw,
            ),
            type: (string) ($data['type'] ?? ''),
        );
    }
}
