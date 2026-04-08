<?php

declare(strict_types=1);

namespace YourVendor\SaloonApiSdk;

use YourVendor\SaloonApiSdk\Requests\ExampleGetRequest;
use YourVendor\SaloonApiSdk\Responses\ExampleResponse;

class SaloonApiSdk
{
    public function __construct(private readonly SaloonConnector $connector) {}

    /**
     * @param  string|null  $baseUrl  Optional API base URL override.
     * @param  string|null  $token  API token (sent via the configured auth header).
     */
    public static function make(?string $baseUrl = null, ?string $token = null): self
    {
        return new self(new SaloonConnector($baseUrl, $token));
    }

    /**
     * Example call — replace with real endpoints for your API.
     *
     * @see ExampleGetRequest
     */
    public function ping(): ExampleResponse
    {
        /** @var ExampleResponse */
        return $this->connector->send(new ExampleGetRequest)->dtoOrFail();
    }
}
