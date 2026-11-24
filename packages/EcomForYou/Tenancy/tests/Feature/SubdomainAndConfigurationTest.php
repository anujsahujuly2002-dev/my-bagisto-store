<?php

use EcomForYou\Tenancy\Exceptions\TenancyMisconfiguredException;
use EcomForYou\Tenancy\Rules\Subdomain;
use EcomForYou\Tenancy\Support\ConfigurationGuard;
use Illuminate\Support\Facades\Validator;

/**
 * Validate a subdomain with the subdomain rule, returning the first error, or null when it passes.
 */
function subdomainError(mixed $value, ?int $ignoreTenantId = null): ?string
{
    $validator = Validator::make(['subdomain' => $value], ['subdomain' => [new Subdomain($ignoreTenantId)]]);

    return $validator->errors()->first('subdomain') ?: null;
}

// ============================================================================
// Subdomain Rule
// ============================================================================

it('should accept a well formed subdomain', function (string $subdomain) {
    expect(subdomainError($subdomain))->toBeNull();
})->with([
    'three characters' => 'abc',
    'letters, numbers and dashes' => 'my-store-2',
    'sixty-three characters' => str_repeat('a', 63),
]);

it('should reject a malformed subdomain', function (mixed $subdomain) {
    expect(subdomainError($subdomain))->toBe(trans('tenancy::app.validation.subdomain.format', ['attribute' => 'subdomain']));
})->with([
    'too short' => 'ab',
    'too long' => str_repeat('a', 64),
    'leading dash' => '-store',
    'trailing dash' => 'store-',
    'uppercase' => 'Store',
    'underscore' => 'my_store',
    'dot' => 'my.store',
    'not a string' => [['store']],
]);

it('should reject a reserved subdomain', function (string $subdomain) {
    expect(subdomainError($subdomain))->toBe(trans('tenancy::app.validation.subdomain.reserved', ['attribute' => 'subdomain']));
})->with(['www', 'admin', 'api', 'support']);

it('should reject a subdomain another store has', function () {
    $this->createTenant(['subdomain' => 'taken-store']);

    expect(subdomainError('taken-store'))->toBe(trans('tenancy::app.validation.subdomain.taken', ['attribute' => 'subdomain']));
});

it('should let a store keep its own subdomain on a rename', function () {
    $tenant = $this->createTenant(['subdomain' => 'own-store']);

    expect(subdomainError('own-store', $tenant->id))->toBeNull();
});

// ============================================================================
// Configuration Guard
// ============================================================================

it('should accept the configuration while tenancy is off whatever it holds', function () {
    config(['tenancy.enabled' => false, 'session.domain' => '.platform.test']);

    app(ConfigurationGuard::class)->check();

    expect(true)->toBeTrue();
});

it('should accept a sound configuration with tenancy on', function () {
    config(['tenancy.enabled' => true, 'session.domain' => null]);

    app(ConfigurationGuard::class)->check();

    expect(true)->toBeTrue();
});

it('should refuse a session cookie shared across stores', function (string $domain) {
    config(['tenancy.enabled' => true, 'session.domain' => $domain]);

    expect(fn () => app(ConfigurationGuard::class)->check())->toThrow(TenancyMisconfiguredException::class, 'SESSION_DOMAIN');
})->with(['.platform.test', 'platform.test']);

it('should refuse a tenant connection built from itself', function () {
    config(['tenancy.enabled' => true, 'tenancy.database.template' => 'tenant']);

    expect(fn () => app(ConfigurationGuard::class)->check())->toThrow(TenancyMisconfiguredException::class);
});

it('should refuse tenancy without a platform domain', function () {
    config(['tenancy.enabled' => true, 'tenancy.platform_domain' => '']);

    expect(fn () => app(ConfigurationGuard::class)->check())->toThrow(TenancyMisconfiguredException::class, 'TENANCY_PLATFORM_DOMAIN');
});
