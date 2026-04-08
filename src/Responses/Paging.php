<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Responses;

readonly class Paging
{
    public function __construct(
        public ?string $nextAfter = null,
        public ?string $nextLink = null,
    ) {}

    /**
     * @param  array<string, mixed>|null  $data
     */
    public static function fromArray(?array $data): self
    {
        if ($data === null) {
            return new self;
        }

        /** @var array{after?: string, link?: string} $next */
        $next = isset($data['next']) && is_array($data['next']) ? $data['next'] : [];

        return new self(
            nextAfter: isset($next['after']) ? (string) $next['after'] : null,
            nextLink: isset($next['link']) ? (string) $next['link'] : null,
        );
    }

    public function hasNextPage(): bool
    {
        return $this->nextAfter !== null;
    }
}
