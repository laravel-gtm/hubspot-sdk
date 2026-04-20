<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\Requests\EnrollContactInSequenceRequest;
use LaravelGtm\HubspotSdk\Responses\SequenceEnrollmentResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('resolves the enrollments endpoint', function (): void {
    $request = new EnrollContactInSequenceRequest(
        sequenceId: '987',
        contactId: '51',
        senderEmail: 'ae@laravel.com',
        userId: '2222222',
    );

    expect($request->resolveEndpoint())->toBe('/automation/sequences/2026-03/enrollments');
});

it('includes userId in the query string', function (): void {
    $request = new EnrollContactInSequenceRequest(
        sequenceId: '987',
        contactId: '51',
        senderEmail: 'ae@laravel.com',
        userId: '2222222',
    );

    $method = new ReflectionMethod(EnrollContactInSequenceRequest::class, 'defaultQuery');
    $query = $method->invoke($request);

    expect($query)->toBe(['userId' => '2222222']);
});

it('serializes the POST body with sequenceId, contactId, and senderEmail', function (): void {
    $request = new EnrollContactInSequenceRequest(
        sequenceId: '987',
        contactId: '51',
        senderEmail: 'ae@laravel.com',
        userId: '2222222',
    );

    $method = new ReflectionMethod(EnrollContactInSequenceRequest::class, 'defaultBody');
    $body = $method->invoke($request);

    expect($body)->toBe([
        'sequenceId' => '987',
        'contactId' => '51',
        'senderEmail' => 'ae@laravel.com',
    ]);
});

it('parses enrollment response', function (): void {
    $connector = new HubspotConnector;
    $connector->withMockClient(new MockClient([
        EnrollContactInSequenceRequest::class => MockResponse::make([
            'id' => 'enroll-1',
            'toEmail' => 'lead@acme.com',
            'enrolledAt' => '2026-04-20T12:00:00Z',
            'updatedAt' => '2026-04-20T12:00:00Z',
        ], 200),
    ]));

    $response = $connector->send(new EnrollContactInSequenceRequest(
        sequenceId: '987',
        contactId: '51',
        senderEmail: 'ae@laravel.com',
        userId: '2222222',
    ))->dtoOrFail();

    expect($response)->toBeInstanceOf(SequenceEnrollmentResponse::class);
    expect($response->id)->toBe('enroll-1');
    expect($response->toEmail)->toBe('lead@acme.com');
    expect($response->enrolledAt)->toBe('2026-04-20T12:00:00Z');
});
