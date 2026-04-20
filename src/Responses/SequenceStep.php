<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class SequenceStep implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public int $stepOrder,
        public int $delayMillis,
        public ?string $actionType = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'stepOrder' => $this->stepOrder,
            'delayMillis' => $this->delayMillis,
            'actionType' => $this->actionType,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            stepOrder: (int) ($data['stepOrder'] ?? 0),
            delayMillis: (int) ($data['delayMillis'] ?? 0),
            actionType: isset($data['actionType']) ? (string) $data['actionType'] : null,
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            updatedAt: isset($data['updatedAt']) ? (string) $data['updatedAt'] : null,
        );
    }
}
