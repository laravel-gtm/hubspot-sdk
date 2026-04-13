<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class SearchCompaniesResponse implements \JsonSerializable
{
    /**
     * @param  list<Company>  $results
     */
    public function __construct(
        public int $total,
        public array $results,
        public Paging $paging,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'total' => $this->total,
            'results' => $this->results,
            'paging' => $this->paging,
        ];
    }

    /**
     * @param  array{total: int, results: list<array<string, mixed>>, paging?: array<string, mixed>|null}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $resultsData */
        $resultsData = $data['results'];

        return new self(
            total: (int) $data['total'],
            results: array_map(
                static fn (array $item): Company => Company::fromArray($item),
                $resultsData,
            ),
            paging: Paging::fromArray($data['paging'] ?? null),
        );
    }
}
