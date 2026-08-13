<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

/**
 * One `from` object's associations in a v4 batch read result.
 */
readonly class AssociationBatchResult implements \JsonSerializable
{
    /**
     * @param  list<AssociationTarget>  $to
     */
    public function __construct(
        public string $fromId,
        public array $to,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'fromId' => $this->fromId,
            'to' => $this->to,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var array{id?: string|int} $from */
        $from = is_array($data['from'] ?? null) ? $data['from'] : [];

        /** @var list<array<string, mixed>> $to */
        $to = is_array($data['to'] ?? null) ? $data['to'] : [];

        return new self(
            fromId: (string) ($from['id'] ?? ''),
            to: array_map(
                static fn (array $target): AssociationTarget => AssociationTarget::fromArray($target),
                $to,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public function toObjectIds(): array
    {
        return array_map(
            static fn (AssociationTarget $target): string => $target->toObjectId,
            $this->to,
        );
    }
}
