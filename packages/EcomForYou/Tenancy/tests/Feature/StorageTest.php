<?php

use EcomForYou\Core\Filesystem\StorageConfigurator;
use EcomForYou\Sitemap\Helpers\SitemapFileUrl;
use EcomForYou\Tenancy\Tasks\FilesystemTask;
use EcomForYou\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->folder = 'tenancy-test-'.Str::lower(Str::random(10));

    config([
        'tenancy.filesystem.local_root' => 'framework/testing/'.$this->folder.'/local/{id}',
        'tenancy.filesystem.disks.public.root' => 'app/public/'.$this->folder.'/{id}',
        'tenancy.filesystem.disks.public.url' => '/storage/'.$this->folder.'/{id}',
        'tenancy.filesystem.disks.private.root' => 'framework/testing/'.$this->folder.'/private/{id}',
        'filesystems.default' => 'public',
        'filesystems.disks.s3.region' => 'us-east-1',
        'filesystems.disks.s3.bucket' => 'platform-media',
    ]);

    app()->forgetInstance(TenantContext::class);

    $this->tenant = $this->createTenant(['database' => DB::connection()->getDatabaseName()]);
});

afterEach(function () {
    app(TenantContext::class)->forget();

    File::deleteDirectory(storage_path('app/public/'.$this->folder));

    File::deleteDirectory(storage_path('framework/testing/'.$this->folder));
});

/**
 * The bytes of a one pixel PNG image.
 */
function onePixelPng(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg==');
}

// ============================================================================
// Disk Roots
// ============================================================================

it('should root the public disk in the store folder and serve it under the store address', function () {
    app(TenantContext::class)->makeCurrent($this->tenant);

    Storage::disk('public')->put('product/1/image.png', onePixelPng());

    expect(File::exists(storage_path('app/public/'.$this->folder.'/'.$this->tenant->id.'/product/1/image.png')))->toBeTrue()
        ->and(Storage::disk('public')->url('product/1/image.png'))
        ->toBe($this->tenant->url().'/storage/'.$this->folder.'/'.$this->tenant->id.'/product/1/image.png');
});

it('should prefix a remote disk with the store folder and give its root back afterwards', function () {
    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    expect(config('filesystems.disks.s3.root'))->toBe('tenants/'.$this->tenant->id)
        ->and(Storage::disk('s3')->path('product/1/image.png'))->toBe('tenants/'.$this->tenant->id.'/product/1/image.png');

    $context->forget();

    expect(config('filesystems.disks.s3.root'))->toBeNull()
        ->and(Storage::disk('s3')->path('product/1/image.png'))->toBe('product/1/image.png');
});

it('should keep the prefix a remote disk already has in front of the store folder', function () {
    config(['filesystems.disks.r2.root' => 'media/', 'filesystems.disks.r2.bucket' => 'platform-media']);

    app()->forgetInstance(TenantContext::class);

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(config('filesystems.disks.r2.root'))->toBe('media/tenants/'.$this->tenant->id);
});

it('should apply the disk the store chose once its disks are rooted, and the platform default afterwards', function () {
    $seenRoot = null;

    $this->mock(StorageConfigurator::class, function ($mock) use (&$seenRoot) {
        $mock->shouldReceive('configure')->andReturnUsing(function () use (&$seenRoot) {
            $seenRoot = config('filesystems.disks.s3.root');

            config(['filesystems.default' => 's3']);
        });
    });

    $context = app(TenantContext::class);

    $context->makeCurrent($this->tenant);

    expect(config('filesystems.default'))->toBe('s3')
        ->and($seenRoot)->toBe('tenants/'.$this->tenant->id);

    $context->forget();

    expect(config('filesystems.default'))->toBe('public');
});

it('should leave a remote disk prefix out of the folders a store owns', function () {
    expect(app(FilesystemTask::class)->directories($this->tenant))->toHaveCount(3)
        ->each->toStartWith(storage_path());
});

// ============================================================================
// Image Cache
// ============================================================================

it('should serve a store its own image through the image cache', function () {
    app(TenantContext::class)->makeCurrent($this->tenant);

    Storage::disk('public')->put('product/1/own.png', onePixelPng());

    $this->get('cache/original/product/1/own.png')
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');
});

it('should never serve another store image through the image cache', function () {
    $other = $this->createTenant();

    File::ensureDirectoryExists($directory = storage_path('app/public/'.$this->folder.'/'.$other->id.'/product/1'));

    File::put($directory.'/theirs.png', onePixelPng());

    app(TenantContext::class)->makeCurrent($this->tenant);

    $this->get('cache/original/'.$this->folder.'/'.$other->id.'/product/1/theirs.png')->assertNotFound();

    $this->get('cache/original/../'.$other->id.'/product/1/theirs.png')->assertNotFound();
});

// ============================================================================
// Urls Built From Stored Paths
// ============================================================================

it('should resolve theme media to the store folder under the store address', function () {
    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(bagisto_theme_storage()->url('theme/1/banner.webp'))
        ->toBe($this->tenant->url().'/storage/'.$this->folder.'/'.$this->tenant->id.'/theme/1/banner.webp')
        ->and(bagisto_theme_storage()->resolveMarkup('<img src="__media__/theme/1/banner.webp">'))
        ->toBe('<img src="/storage/'.$this->folder.'/'.$this->tenant->id.'/theme/1/banner.webp">');
});

it('should link sitemap files in the store folder, and in the shared folder for the single store', function () {
    $file = 'sitemaps/default/sitemap-1-1.xml';

    expect(app(SitemapFileUrl::class)->forChannel('https://shop.test/', $file))->toBe('https://shop.test/storage/'.$file);

    app(TenantContext::class)->makeCurrent($this->tenant);

    expect(app(SitemapFileUrl::class)->forChannel($this->tenant->url(), $file))
        ->toBe($this->tenant->url().'/storage/'.$this->folder.'/'.$this->tenant->id.'/'.$file);
});
