<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class Association implements \JsonSerializable
{
    public function __construct(
        public string $id,
        public string $type,
    ) {}

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            type: (string) $data['type'],
        );
    }
}
