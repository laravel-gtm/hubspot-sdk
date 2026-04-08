<?php

declare(strict_types=1);

use Saloon\Config;
use YourVendor\SaloonApiSdk\Tests\TestCase;

Config::preventStrayRequests();

uses(TestCase::class)->in('Feature');
