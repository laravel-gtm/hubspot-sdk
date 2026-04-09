<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class ListDealPropertiesResponse implements \JsonSerializable
{
    /**
     * @param  list<CrmProperty>  $results
     */
    public function __construct(
        public array $results,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
        ];
    }

    /**
     * @param  array{results: list<array<string, mixed>>}  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $resultsData */
        $resultsData = $data['results'];

        return new self(
            results: array_map(
                static fn (array $item): CrmProperty => CrmProperty::fromArray($item),
                $resultsData,
            ),
        );
    }
}
