<?php

use EcomForYou\Tenancy\Enums\TenantStatus;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    config([
        'tenancy.enabled' => true,
        'tenancy.platform_domain' => 'platform.test',
        'tenancy.central_hosts' => ['platform.test', 'www.platform.test'],
    ]);

    Route::get('tenancy-probe', fn () => response()->json([
        'tenant' => app(TenantContext::class)->current()?->subdomain,
    ]));
});

afterEach(function () {
    app(TenantContext::class)->forget();
});

// ============================================================================
// Identification
// ============================================================================

it('should make the store its subdomain names current', function () {
    $this->createTenant(['subdomain' => 'store-one', 'database' => DB::connection()->getDatabaseName()]);

    $this->get('http://store-one.platform.test/tenancy-probe')
        ->assertOk()
        ->assertJson(['tenant' => 'store-one']);
});

it('should match the subdomain whatever the case of the host', function () {
    $this->createTenant(['subdomain' => 'store-case', 'database' => DB::connection()->getDatabaseName()]);

    $this->get('http://STORE-CASE.Platform.Test/tenancy-probe')
        ->assertOk()
        ->assertJson(['tenant' => 'store-case']);
});

it('should identify no store on a central host', function (string $host) {
    $this->get("http://{$host}/tenancy-probe")
        ->assertOk()
        ->assertJson(['tenant' => null]);
})->with(['platform.test', 'www.platform.test']);

it('should forget a store left current before a central host request', function () {
    app(TenantContext::class)->makeCurrent($this->createTenant(['database' => DB::connection()->getDatabaseName()]));

    $this->get('http://platform.test/tenancy-probe')
        ->assertOk()
        ->assertJson(['tenant' => null]);
});

it('should identify nothing while tenancy is off', function () {
    config(['tenancy.enabled' => false]);

    $this->createTenant(['subdomain' => 'store-off']);

    $this->get('http://store-off.platform.test/tenancy-probe')
        ->assertOk()
        ->assertJson(['tenant' => null]);
});

// ============================================================================
// Stores That Cannot Be Opened
// ============================================================================

it('should answer not found for a host that names no store', function (string $host) {
    $this->get("http://{$host}/tenancy-probe")
        ->assertNotFound()
        ->assertSee(trans('tenancy::app.errors.not-found.title'));
})->with([
    'unknown subdomain' => 'nobody.platform.test',
    'nested subdomain' => 'shop.store-one.platform.test',
    'another domain' => 'example.com',
    'a domain ending like the platform' => 'notplatform.test',
]);

it('should answer not found for a reserved subdomain even when a record claims it', function () {
    $this->createTenant(['subdomain' => 'admin']);

    $this->get('http://admin.platform.test/tenancy-probe')->assertNotFound();
});

it('should answer that a suspended store is unavailable', function () {
    $this->createTenant(['subdomain' => 'store-suspended', 'status' => TenantStatus::SUSPENDED]);

    $this->get('http://store-suspended.platform.test/tenancy-probe')
        ->assertForbidden()
        ->assertSee(trans('tenancy::app.errors.suspended.title'));
});

it('should ask the visitor to come back while a store is being set up', function () {
    $this->createTenant(['subdomain' => 'store-building', 'status' => TenantStatus::PROVISIONING]);

    $this->get('http://store-building.platform.test/tenancy-probe')
        ->assertServiceUnavailable()
        ->assertHeader('Retry-After', '30')
        ->assertSee(trans('tenancy::app.errors.provisioning.title'));
});

it('should answer not found for a store that never opened', function (string $status) {
    $this->createTenant(['subdomain' => 'store-closed', 'status' => $status]);

    $this->get('http://store-closed.platform.test/tenancy-probe')->assertNotFound();
})->with([TenantStatus::PENDING_VERIFICATION, TenantStatus::FAILED]);
