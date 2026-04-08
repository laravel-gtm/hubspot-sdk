<?php

declare(strict_types=1);

namespace YourVendor\SaloonApiSdk\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use YourVendor\SaloonApiSdk\Laravel\SaloonApiSdkServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SaloonApiSdkServiceProvider::class];
    }
}
