<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Tests;

use LaravelGtm\HubspotSdk\Laravel\HubspotServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [HubspotServiceProvider::class];
    }
}
