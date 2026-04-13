<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\GetOwnerRequest;

it('resolves the owner endpoint', function (): void {
    $request = new GetOwnerRequest('87644597');
    expect($request->resolveEndpoint())->toBe('/crm/v3/owners/87644597');
});
