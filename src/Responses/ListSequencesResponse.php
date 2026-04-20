<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class ListSequencesResponse implements \JsonSerializable
{
    /**
     * @param  list<Sequence>  $results
     */
    public function __construct(
        public array $results,
        public Paging $paging,
        public ?int $total = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'total' => $this->total,
            'results' => $this->results,
            'paging' => $this->paging,
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  array{total?: int, results: list<array<string, mixed>>, paging?: array<string, mixed>|null}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $resultsData */
        $resultsData = $data['results'];

        return new self(
            results: array_map(
                static fn (array $item): Sequence => Sequence::fromArray($item),
                $resultsData,
            ),
            paging: Paging::fromArray($data['paging'] ?? null),
            total: isset($data['total']) ? (int) $data['total'] : null,
        );
    }
}
