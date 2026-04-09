<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class ListContactsResponse implements \JsonSerializable
{
    /**
     * @param  list<Contact>  $results
     */
    public function __construct(
        public array $results,
        public Paging $paging,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
            'paging' => $this->paging,
        ];
    }

    /**
     * @param  array{results: list<array<string, mixed>>, paging?: array<string, mixed>|null}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $resultsData */
        $resultsData = $data['results'];

        return new self(
            results: array_map(
                static fn (array $item): Contact => Contact::fromArray($item),
                $resultsData,
            ),
            paging: Paging::fromArray($data['paging'] ?? null),
        );
    }
}
