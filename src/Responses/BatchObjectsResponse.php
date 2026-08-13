<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class BatchObjectsResponse implements \JsonSerializable
{
    /**
     * @param  list<CrmObject>  $results
     * @param  list<array<string, mixed>>  $errors
     */
    public function __construct(
        public array $results,
        public ?string $status = null,
        public int $numErrors = 0,
        public array $errors = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'results' => $this->results,
            'status' => $this->status,
            'numErrors' => $this->numErrors,
            'errors' => $this->errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        /** @var list<array<string, mixed>> $resultsData */
        $resultsData = is_array($data['results'] ?? null) ? $data['results'] : [];

        /** @var list<array<string, mixed>> $errors */
        $errors = is_array($data['errors'] ?? null) ? $data['errors'] : [];

        return new self(
            results: array_map(
                static fn (array $item): CrmObject => CrmObject::fromArray($item),
                $resultsData,
            ),
            status: isset($data['status']) ? (string) $data['status'] : null,
            numErrors: (int) ($data['numErrors'] ?? 0),
            errors: $errors,
        );
    }

    /**
     * IDs present in the results. Batch reads silently omit IDs that don't
     * exist (or were merged away) — diff against the requested IDs to detect
     * deletions.
     *
     * @return list<string>
     */
    public function resultIds(): array
    {
        return array_map(
            static fn (CrmObject $object): string => $object->id,
            $this->results,
        );
    }
}
