<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

/**
 * One `to` entry in a v4 associations result: the associated object's ID
 * plus the association types linking it to the `from` object.
 */
readonly class AssociationTarget implements \JsonSerializable
{
    /**
     * @param  list<AssociationType>  $associationTypes
     */
    public function __construct(
        public string $toObjectId,
        public array $associationTypes,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'toObjectId' => $this->toObjectId,
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
            toObjectId: (string) ($data['toObjectId'] ?? ''),
            associationTypes: array_map(
                static fn (array $type): AssociationType => AssociationType::fromArray($type),
                $types,
            ),
        );
    }
}
