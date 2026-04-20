<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Requests;

use LaravelGtm\HubspotSdk\Responses\SequenceEnrollmentResponse;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;

class EnrollContactInSequenceRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly string $sequenceId,
        private readonly string $contactId,
        private readonly string $senderEmail,
        private readonly string $userId,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/automation/sequences/2026-03/enrollments';
    }

    /**
     * @return array<string, string>
     */
    protected function defaultQuery(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function defaultBody(): array
    {
        return [
            'sequenceId' => $this->sequenceId,
            'contactId' => $this->contactId,
            'senderEmail' => $this->senderEmail,
        ];
    }

    public function createDtoFromResponse(Response $response): SequenceEnrollmentResponse
    {
        /** @var array<string, mixed> $data */
        $data = $response->json();

        return SequenceEnrollmentResponse::fromArray($data);
    }
}
