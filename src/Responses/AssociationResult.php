<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class AssociationResult implements \JsonSerializable
{
    /**
     * @param  list<mixed>  $labels
     */
    public function __construct(
        public string $fromObjectId,
        public string $toObjectId,
        public array $labels = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'fromObjectId' => $this->fromObjectId,
            'toObjectId' => $this->toObjectId,
            'labels' => $this->labels,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        // The default-association endpoint wraps its single result in a
        // "results" envelope, matching HubSpot's batch association response shape.
        if (isset($data['results']) && is_array($data['results']) && $data['results'] !== []) {
            /** @var array<string, mixed> $first */
            $first = $data['results'][0];
            $data = $first;
        }

        /** @var list<mixed> $labels */
        $labels = is_array($data['labels'] ?? null) ? $data['labels'] : [];

        return new self(
            fromObjectId: (string) ($data['fromObjectId'] ?? ''),
            toObjectId: (string) ($data['toObjectId'] ?? ''),
            labels: $labels,
        );
    }
}
