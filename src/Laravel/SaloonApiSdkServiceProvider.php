<?php

declare(strict_types=1);

namespace YourVendor\SaloonApiSdk\Laravel;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;
use YourVendor\SaloonApiSdk\SaloonApiSdk;
use YourVendor\SaloonApiSdk\SaloonConnector;

class SaloonApiSdkServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/saloon-api-sdk-boilerplate.php', 'saloon-api-sdk-boilerplate');

        $this->app->singleton(SaloonConnector::class, function (): SaloonConnector {
            $configRepository = $this->app->make(ConfigRepository::class);
            $cacheFactory = $this->app->make(CacheFactory::class);
            /** @var array<string, mixed> $config */
            $config = (array) $configRepository->get('saloon-api-sdk-boilerplate', []);

            return new SaloonConnector(
                isset($config['base_url']) ? (string) $config['base_url'] : null,
                isset($config['token']) ? (string) $config['token'] : null,
                isset($config['auth_header']) ? (string) $config['auth_header'] : 'X-Api-Key',
                new LaravelCacheStore($cacheFactory->store()),
            );
        });

        $this->app->singleton(SaloonApiSdk::class, function (): SaloonApiSdk {
            return new SaloonApiSdk($this->app->make(SaloonConnector::class));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/saloon-api-sdk-boilerplate.php' => $this->app->configPath('saloon-api-sdk-boilerplate.php'),
            ], 'saloon-api-sdk-boilerplate-config');
        }
    }
}
