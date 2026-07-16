<?php

declare(strict_types=1);

use LaravelGtm\HubspotSdk\Requests\AssociateContactWithCompanyRequest;
use Saloon\Enums\Method;

it('uses the PUT method', function (): void {
    $request = new AssociateContactWithCompanyRequest('501', '20787072317');

    expect($request->getMethod())->toBe(Method::PUT);
});

it('resolves the default association endpoint with contact and company ids', function (): void {
    $request = new AssociateContactWithCompanyRequest('501', '20787072317');

    expect($request->resolveEndpoint())->toBe(
        '/crm/v4/objects/contacts/501/associations/default/companies/20787072317',
    );
});
