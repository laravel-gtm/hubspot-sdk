<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class Sequence implements \JsonSerializable
{
    /**
     * @param  list<SequenceStep>  $steps
     */
    public function __construct(
        public string $id,
        public string $name,
        public ?string $userId = null,
        public ?string $folderId = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public array $steps = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'userId' => $this->userId,
            'folderId' => $this->folderId,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'steps' => $this->steps === [] ? null : $this->steps,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $stepsData */
        $stepsData = isset($data['steps']) && is_array($data['steps']) ? $data['steps'] : [];

        return new self(
            id: (string) $data['id'],
            name: (string) ($data['name'] ?? ''),
            userId: isset($data['userId']) ? (string) $data['userId'] : null,
            folderId: isset($data['folderId']) ? (string) $data['folderId'] : null,
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            updatedAt: isset($data['updatedAt']) ? (string) $data['updatedAt'] : null,
            steps: array_map(
                static fn (array $step): SequenceStep => SequenceStep::fromArray($step),
                $stepsData,
            ),
        );
    }
}
