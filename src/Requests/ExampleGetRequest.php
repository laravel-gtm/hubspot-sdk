<?php

declare(strict_types=1);

namespace YourVendor\SaloonApiSdk\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use YourVendor\SaloonApiSdk\Responses\ExampleResponse;

/**
 * Example request — replace path and DTO with your API.
 *
 * `GET /v1/ping`
 */
class ExampleGetRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/v1/ping';
    }

    public function createDtoFromResponse(Response $response): ExampleResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return ExampleResponse::fromArray($data);
    }
}
