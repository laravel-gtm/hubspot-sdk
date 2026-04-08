<?php

declare(strict_types=1);

namespace LaravelGtm\HubspotSdk\Laravel;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Support\ServiceProvider;
use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;
use Saloon\RateLimitPlugin\Stores\LaravelCacheStore;

class HubspotServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/hubspot.php', 'hubspot');

        $this->app->singleton(HubspotConnector::class, function (): HubspotConnector {
            $configRepository = $this->app->make(ConfigRepository::class);
            $cacheFactory = $this->app->make(CacheFactory::class);
            /** @var array<string, mixed> $config */
            $config = (array) $configRepository->get('hubspot', []);
            /** @var array<string, mixed> $rateLimitConfig */
            $rateLimitConfig = isset($config['rate_limit']) && is_array($config['rate_limit']) ? $config['rate_limit'] : [];

            return new HubspotConnector(
                isset($config['base_url']) ? (string) $config['base_url'] : null,
                isset($config['api_key']) ? (string) $config['api_key'] : null,
                new LaravelCacheStore($cacheFactory->store()),
                isset($rateLimitConfig['burst']) ? (int) $rateLimitConfig['burst'] : 190,
                isset($rateLimitConfig['daily']) ? (int) $rateLimitConfig['daily'] : 1000000,
            );
        });

        $this->app->singleton(HubspotSdk::class, function (): HubspotSdk {
            $configRepository = $this->app->make(ConfigRepository::class);
            /** @var array<string, mixed> $config */
            $config = (array) $configRepository->get('hubspot', []);
            /** @var array<string, mixed> $oauthConfig */
            $oauthConfig = isset($config['oauth']) && is_array($config['oauth']) ? $config['oauth'] : [];
            $tokenColumn = isset($oauthConfig['token_column']) ? (string) $oauthConfig['token_column'] : 'hubspot_access_token';

            return new HubspotSdk(
                $this->app->make(HubspotConnector::class),
                $tokenColumn,
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/hubspot.php' => $this->app->configPath('hubspot.php'),
            ], 'hubspot-config');
        }
    }
}
