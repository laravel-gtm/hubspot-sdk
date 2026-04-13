<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class Owner implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public ?string $email = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $userId = null,
        public bool $archived = false,
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
            'email' => $this->email,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'userId' => $this->userId,
            'archived' => $this->archived,
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
            email: isset($data['email']) ? (string) $data['email'] : null,
            firstName: isset($data['firstName']) ? (string) $data['firstName'] : null,
            lastName: isset($data['lastName']) ? (string) $data['lastName'] : null,
            userId: isset($data['userId']) ? (string) $data['userId'] : null,
            archived: (bool) ($data['archived'] ?? false),
            createdAt: isset($data['createdAt']) ? (string) $data['createdAt'] : null,
            updatedAt: isset($data['updatedAt']) ? (string) $data['updatedAt'] : null,
        );
    }
}
