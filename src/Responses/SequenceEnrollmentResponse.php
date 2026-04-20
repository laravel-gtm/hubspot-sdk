<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class SequenceEnrollmentResponse implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public ?string $toEmail = null,
        public ?string $enrolledAt = null,
        public ?string $updatedAt = null,
        public ?string $sequenceId = null,
        public ?string $sequenceName = null,
        public ?string $enrolledBy = null,
        public ?string $enrolledByEmail = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'id' => $this->id,
            'toEmail' => $this->toEmail,
            'enrolledAt' => $this->enrolledAt,
            'updatedAt' => $this->updatedAt,
            'sequenceId' => $this->sequenceId,
            'sequenceName' => $this->sequenceName,
            'enrolledBy' => $this->enrolledBy,
            'enrolledByEmail' => $this->enrolledByEmail,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            toEmail: isset($data['toEmail']) ? (string) $data['toEmail'] : null,
            enrolledAt: isset($data['enrolledAt']) ? (string) $data['enrolledAt'] : null,
            updatedAt: isset($data['updatedAt']) ? (string) $data['updatedAt'] : null,
            sequenceId: isset($data['sequenceId']) ? (string) $data['sequenceId'] : null,
            sequenceName: isset($data['sequenceName']) ? (string) $data['sequenceName'] : null,
            enrolledBy: isset($data['enrolledBy']) ? (string) $data['enrolledBy'] : null,
            enrolledByEmail: isset($data['enrolledByEmail']) ? (string) $data['enrolledByEmail'] : null,
        );
    }
}
