<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class GetContactResponse implements \JsonSerializable
{
    /**
     * @param  array<string, string|null>  $properties
     * @param  array<string, list<Association>>  $associations
     */
    public function __construct(
        public string $id,
        public array $properties,
        public string $createdAt,
        public string $updatedAt,
        public bool $archived,
        public ?string $archivedAt = null,
        public array $associations = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'properties' => $this->properties,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'archived' => $this->archived,
            'archivedAt' => $this->archivedAt,
            'associations' => $this->associations !== [] ? $this->associations : null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, string|null> $properties */
        $properties = isset($data['properties']) && is_array($data['properties'])
            ? $data['properties']
            : [];

        /** @var array<string, list<Association>> $associations */
        $associations = [];

        if (isset($data['associations']) && is_array($data['associations'])) {
            /**
             * @var string $objectType
             * @var array<string, mixed> $assocData
             */
            foreach ($data['associations'] as $objectType => $assocData) {
                /** @var list<array<string, mixed>> $results */
                $results = isset($assocData['results']) && is_array($assocData['results'])
                    ? $assocData['results']
                    : [];

                $associations[$objectType] = array_map(
                    static fn (array $item): Association => Association::fromArray($item),
                    $results,
                );
            }
        }

        return new self(
            id: (string) $data['id'],
            properties: $properties,
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
            archived: (bool) ($data['archived'] ?? false),
            archivedAt: isset($data['archivedAt']) ? (string) $data['archivedAt'] : null,
            associations: $associations,
        );
    }
}
