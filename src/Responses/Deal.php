<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class Deal
{
    /**
     * @param  array<string, string|null>  $properties
     */
    public function __construct(
        public string $id,
        public array $properties,
        public string $createdAt,
        public string $updatedAt,
        public bool $archived,
        public ?string $archivedAt = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array<string, string|null> $properties */
        $properties = isset($data['properties']) && is_array($data['properties'])
            ? $data['properties']
            : [];

        return new self(
            id: (string) $data['id'],
            properties: $properties,
            createdAt: (string) $data['createdAt'],
            updatedAt: (string) $data['updatedAt'],
            archived: (bool) ($data['archived'] ?? false),
            archivedAt: isset($data['archivedAt']) ? (string) $data['archivedAt'] : null,
        );
    }
}
