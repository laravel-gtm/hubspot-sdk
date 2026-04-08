<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LaravelGtm\HubspotSdk\HubspotConnector;
use LaravelGtm\HubspotSdk\HubspotSdk;

it('creates a new sdk instance with user oauth token', function (): void {
    $user = new class extends Model implements Authenticatable
    {
        use Illuminate\Auth\Authenticatable;

        protected $attributes = ['hubspot_access_token' => 'user-oauth-token-123'];
    };

    $connector = new HubspotConnector('https://api.hubapi.com', 'service-token');
    $sdk = new HubspotSdk($connector);
    $userSdk = $sdk->forUser($user);

    expect($userSdk)->not->toBe($sdk);
    expect($userSdk)->toBeInstanceOf(HubspotSdk::class);
});

it('uses a custom token column', function (): void {
    $user = new class extends Model implements Authenticatable
    {
        use Illuminate\Auth\Authenticatable;

        protected $attributes = ['custom_token_col' => 'custom-token-456'];
    };

    $connector = new HubspotConnector('https://api.hubapi.com', 'service-token');
    $sdk = new HubspotSdk($connector, 'custom_token_col');
    $userSdk = $sdk->forUser($user);

    expect($userSdk)->toBeInstanceOf(HubspotSdk::class);
});

it('throws when user is not an eloquent model', function (): void {
    $user = new class implements Authenticatable
    {
        public function getAuthIdentifierName(): string
        {
            return 'id';
        }

        public function getAuthIdentifier(): mixed
        {
            return 1;
        }

        public function getAuthPassword(): string
        {
            return '';
        }

        public function getRememberToken(): string
        {
            return '';
        }

        public function setRememberToken($value): void {}

        public function getRememberTokenName(): string
        {
            return '';
        }

        public function getAuthPasswordName(): string
        {
            return 'password';
        }
    };

    $connector = new HubspotConnector('https://api.hubapi.com', 'token');
    $sdk = new HubspotSdk($connector);
    $sdk->forUser($user);
})->throws(InvalidArgumentException::class);

it('throws when user has no oauth token', function (): void {
    $user = new class extends Model implements Authenticatable
    {
        use Illuminate\Auth\Authenticatable;

        protected $attributes = ['hubspot_access_token' => null];
    };

    $connector = new HubspotConnector('https://api.hubapi.com', 'token');
    $sdk = new HubspotSdk($connector);
    $sdk->forUser($user);
})->throws(RuntimeException::class);
