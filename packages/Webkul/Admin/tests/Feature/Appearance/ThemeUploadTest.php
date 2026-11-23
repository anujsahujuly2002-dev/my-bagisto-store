<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Theme\Jobs\InstallUploadedTheme;
use Webkul\Theme\Models\Section;
use Webkul\Theme\Models\Theme;
use Webkul\Theme\Models\ThemeSchedule;
use Webkul\Theme\ThemeCatalog;
use Webkul\Theme\ThemeRegistry;
use Webkul\Theme\Themes;
use Webkul\Theme\ThemeStorage;
use Webkul\Theme\Upload\HtmlConverter;
use Webkul\Theme\Upload\ThemeInstaller;
use Webkul\Theme\Upload\ThemeUploads;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

/**
 * The codes of the themes a test uploaded, so their local releases can be removed after it.
 */
$GLOBALS['uploadedThemeCodes'] = [];

afterEach(function () {
    foreach ($GLOBALS['uploadedThemeCodes'] as $code) {
        File::deleteDirectory(app(ThemeInstaller::class)->directory($code));
    }

    $GLOBALS['uploadedThemeCodes'] = [];

    if (Queue::getFacadeRoot() instanceof QueueFake) {
        foreach (Queue::pushed(InstallUploadedTheme::class) as $job) {
            File::delete($job->archivePath);
        }
    }
});

/**
 * A fresh theme code, remembered for clean up.
 */
function uploadCode(): string
{
    return $GLOBALS['uploadedThemeCodes'][] = 'upload-'.Str::lower(Str::random(10));
}

/**
 * The files of a template archive, a header, footer and home content around the given main markup.
 */
function uploadFiles(string $code, string $main = '<h1>Template Welcome</h1>'): array
{
    return [
        'theme.json' => json_encode(['name' => 'Uploaded Shop', 'code' => $code, 'version' => '1.2.0']),
        'index.html' => <<<HTML
            <!DOCTYPE html>
            <html>
                <head>
                    <title>Uploaded</title>
                    <link rel="stylesheet" href="css/style.css">
                </head>
                <body>
                    <header><p>Template Header Text</p><div data-bagisto="cart"></div></header>
                    <main>{$main}</main>
                    <footer><p>Template Footer Text</p></footer>
                    <script>console.log('template ready');</script>
                </body>
            </html>
            HTML,
        'css/style.css' => 'body { color: red; }',
        'about.html' => '<p>About</p>',
    ];
}

/**
 * Pack the files into an uploadable zip.
 */
function uploadArchive(array $files, string $name = 'template.zip'): UploadedFile
{
    $path = sys_get_temp_dir().'/theme-upload-'.Str::random(12).'.zip';

    $zip = new ZipArchive;

    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

    foreach ($files as $entry => $contents) {
        $zip->addFromString($entry, $contents);
    }

    $zip->close();

    return new UploadedFile($path, $name, 'application/zip', null, true);
}

/**
 * Upload the archive as a signed in admin.
 */
function postArchive(UploadedFile $archive)
{
    return post(route('admin.appearance.themes.upload'), ['archive' => $archive], ['Accept' => 'application/json']);
}

/**
 * Point the default channel at a theme and render the storefront home page with it, as a fresh request would.
 */
function visitWithTheme(string $code)
{
    $channel = core()->getDefaultChannel();

    $channel->theme = $code;

    $channel->save();

    core()->setCurrentChannel($channel);

    app(ThemeRegistry::class)->merge();

    app()->forgetInstance(Themes::class);

    Facade::clearResolvedInstance(Themes::class);

    return get(route('shop.home.index'));
}

// ============================================================================
// Installing
// ============================================================================

it('should install an uploaded template as a theme', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.appearance.themes.index'));

    $theme = Theme::query()->where('code', $code)->firstOrFail();

    $release = $theme->manifest['release'];

    $directory = app(ThemeInstaller::class)->directory($code).'/releases/'.$release;

    expect($theme->name)->toBe('Uploaded Shop')
        ->and($theme->version)->toBe('1.2.0')
        ->and($theme->parent)->toBe('default')
        ->and($theme->source)->toBe(Theme::SOURCE_UPLOAD)
        ->and(File::exists($directory.'/page.json'))->toBeTrue()
        ->and(File::exists($directory.'/views/home/index.blade.php'))->toBeTrue()
        ->and(File::exists($directory.'/views/components/layouts/header/index.blade.php'))->toBeTrue();

    Storage::assertExists('themes/'.$code.'/assets/'.$release.'/css/style.css');

    Storage::assertMissing('themes/'.$code.'/assets/'.$release.'/about.html');
});

it('should list an uploaded theme in the gallery', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    app(ThemeRegistry::class)->merge();

    get(route('admin.appearance.themes.index'))
        ->assertOk()
        ->assertSee('Uploaded Shop');
});

it('should name a theme after its archive when it ships no manifest', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    $files = uploadFiles($code);

    unset($files['theme.json']);

    postArchive(uploadArchive($files, $code.'.zip'))->assertOk();

    expect(Theme::query()->where('code', $code)->value('name'))->toBe(Str::headline($code));
});

it('should replace an earlier upload of the same code and keep one release to roll back to', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    $releases = [];

    foreach (range(1, 3) as $attempt) {
        postArchive(uploadArchive(uploadFiles($code, '<h1>Version '.$attempt.'</h1>')))->assertOk();

        $releases[] = Theme::query()->where('code', $code)->value('manifest')['release'];
    }

    $theme = Theme::query()->where('code', $code)->sole();

    $directory = app(ThemeInstaller::class)->directory($code).'/releases/';

    expect($theme->manifest['release'])->toBe($releases[2])
        ->and($theme->manifest['previous_release'])->toBe($releases[1])
        ->and(File::isDirectory($directory.$releases[2]))->toBeTrue()
        ->and(File::isDirectory($directory.$releases[1]))->toBeTrue()
        ->and(File::isDirectory($directory.$releases[0]))->toBeFalse();

    Storage::assertMissing('themes/'.$code.'/assets/'.$releases[0].'/css/style.css');
});

// ============================================================================
// Rendering
// ============================================================================

it('should render the storefront with the uploaded template', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    $release = Theme::query()->where('code', $code)->value('manifest')['release'];

    visitWithTheme($code)
        ->assertOk()
        ->assertSee('Template Header Text')
        ->assertSee('Template Welcome')
        ->assertSee('Template Footer Text')
        ->assertSee('data-bagisto="cart"', false)
        ->assertSee('themes/'.$code.'/assets/'.$release.'/css/style.css', false)
        ->assertSee('template ready')
        ->assertDontSee('v-header-switcher', false);
});

it('should keep the default header and footer when the template has none', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive([
        'theme.json' => json_encode(['code' => $code]),
        'index.html' => '<html><body><h1>Just Content</h1></body></html>',
    ]))->assertOk();

    visitWithTheme($code)
        ->assertOk()
        ->assertSee('Just Content')
        ->assertSee('v-header-switcher', false);
});

it('should render template markup as text rather than code', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code, "<p>@php echo strrev('denwp'); @endphp</p><p>{{ 1111 * 3 }}</p><?php echo strrev('denwp'); ?>")))->assertOk();

    visitWithTheme($code)
        ->assertOk()
        ->assertSee("strrev('denwp')", false)
        ->assertSee("{\u{200B}{ 1111 * 3 }}", false)
        ->assertDontSee('{{ 1111 * 3 }}', false)
        ->assertDontSee('pwned')
        ->assertDontSee('3333');
});

// ============================================================================
// Refusing
// ============================================================================

it('should require an archive', function () {
    $this->loginAsAdmin();

    post(route('admin.appearance.themes.upload'), [], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('archive');
});

it('should refuse a file that is not a zip', function () {
    $this->loginAsAdmin();

    postArchive(UploadedFile::fake()->createWithContent('template.zip', 'plain text'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('archive');
});

it('should refuse an archive with an unsafe entry', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code) + ['shell.php' => '<?php system($_GET["c"]);']))
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.upload.errors.unsafe-path', ['path' => 'shell.php']));

    expect(Theme::query()->where('code', $code)->exists())->toBeFalse();
});

it('should refuse an archive without an index page', function () {
    $this->loginAsAdmin();

    postArchive(uploadArchive(['theme.json' => json_encode(['code' => uploadCode()]), 'css/style.css' => 'body {}']))
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.upload.errors.missing-index'));
});

it('should refuse to replace a theme built into the store', function () {
    $this->loginAsAdmin();

    postArchive(uploadArchive(uploadFiles('default')))
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.upload.errors.code-taken', ['code' => 'default']));

    expect(Theme::query()->where('code', 'default')->exists())->toBeFalse();
});

it('should refuse a code that is not a slug', function () {
    $this->loginAsAdmin();

    postArchive(uploadArchive(uploadFiles('Bad Code!')))
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.upload.errors.invalid-code', ['code' => 'Bad Code!']));
});

// ============================================================================
// Queueing
// ============================================================================

it('should queue a checked archive and report it as processing until the queue installs it', function () {
    Queue::fake();

    $this->loginAsAdmin();

    $code = uploadCode();

    $response = postArchive(uploadArchive(uploadFiles($code)))
        ->assertAccepted()
        ->assertJsonPath('status', ThemeUploads::STATUS_QUEUED)
        ->assertJsonPath('message', trans('admin::app.appearance.themes.upload.processing'));

    expect(Theme::query()->where('code', $code)->exists())->toBeFalse();

    Queue::assertPushed(InstallUploadedTheme::class, function ($job) use ($response) {
        expect(File::exists($job->archivePath))->toBeTrue()
            ->and($response->json('status_url'))->toBe(route('admin.appearance.themes.upload_status', $job->uploadId));

        return true;
    });

    get($response->json('status_url'))
        ->assertAccepted()
        ->assertJsonPath('status', ThemeUploads::STATUS_QUEUED);
});

it('should report a queued upload as installed once the job has run', function () {
    Queue::fake();

    $this->loginAsAdmin();

    $code = uploadCode();

    $response = postArchive(uploadArchive(uploadFiles($code)))->assertAccepted();

    $job = Queue::pushed(InstallUploadedTheme::class)->first();

    app()->call([$job, 'handle']);

    get($response->json('status_url'))
        ->assertOk()
        ->assertJsonPath('status', ThemeUploads::STATUS_INSTALLED)
        ->assertJsonPath('redirect_url', route('admin.appearance.themes.index'));

    expect(Theme::query()->where('code', $code)->exists())->toBeTrue()
        ->and(File::exists($job->archivePath))->toBeFalse();
});

it('should refuse an unsafe archive before queueing it', function () {
    Queue::fake();

    $this->loginAsAdmin();

    postArchive(uploadArchive(uploadFiles(uploadCode()) + ['shell.php' => '<?php echo 1;']))->assertUnprocessable();

    Queue::assertNothingPushed();
});

it('should hide a queued upload\'s status from another admin', function () {
    Queue::fake();

    $this->loginAsAdmin();

    $response = postArchive(uploadArchive(uploadFiles(uploadCode())))->assertAccepted();

    $this->loginAsAdmin();

    get($response->json('status_url'))->assertNotFound();
});

// ============================================================================
// Rolling Back
// ============================================================================

it('should roll an uploaded theme back to its previous release', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code, '<h1>Version One</h1>')))->assertOk();

    $first = Theme::query()->where('code', $code)->value('manifest')['release'];

    postArchive(uploadArchive(uploadFiles($code, '<h1>Version Two</h1>')))->assertOk();

    $second = Theme::query()->where('code', $code)->value('manifest')['release'];

    post(route('admin.appearance.themes.rollback', $code))
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.appearance.themes.index'));

    $theme = Theme::query()->where('code', $code)->sole();

    expect($theme->manifest['release'])->toBe($first)
        ->and($theme->manifest['previous_release'])->toBe($second)
        ->and($theme->assets_path)->toBe('themes/'.$code.'/assets/'.$first)
        ->and($theme->views_path)->toEndWith('/releases/'.$first.'/views');

    visitWithTheme($code)
        ->assertOk()
        ->assertSee('Version One')
        ->assertDontSee('Version Two');
});

it('should refuse to roll back a theme that has only one release', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    post(route('admin.appearance.themes.rollback', $code), [], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.manage.rollback-unavailable'));
});

it('should offer a roll back in the gallery only once a theme has an earlier release', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    app(ThemeRegistry::class)->merge();

    $theme = fn () => collect(app(ThemeCatalog::class)->all())->firstWhere('code', $code);

    expect($theme()['is_uploaded'])->toBeTrue()
        ->and($theme()['can_rollback'])->toBeFalse();

    postArchive(uploadArchive(uploadFiles($code, '<h1>Again</h1>')))->assertOk();

    expect($theme()['can_rollback'])->toBeTrue();
});

// ============================================================================
// Deleting
// ============================================================================

it('should delete an uploaded theme with its files', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    $release = Theme::query()->where('code', $code)->value('manifest')['release'];

    delete(route('admin.appearance.themes.delete', $code))
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.appearance.themes.index'));

    expect(Theme::query()->where('code', $code)->exists())->toBeFalse()
        ->and(File::isDirectory(app(ThemeInstaller::class)->directory($code)))->toBeFalse();

    Storage::assertMissing('themes/'.$code.'/assets/'.$release.'/css/style.css');
});

it('should refuse to delete a theme a channel runs', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    $channel = core()->getDefaultChannel();

    $channel->theme = $code;

    $channel->save();

    app(ThemeRegistry::class)->merge();

    delete(route('admin.appearance.themes.delete', $code), [], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.manage.delete-in-use', ['channels' => $channel->name]));

    expect(Theme::query()->where('code', $code)->exists())->toBeTrue();
});

it('should remove the switches scheduled to a deleted theme', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    $schedule = ThemeSchedule::query()->create([
        'channel_id' => core()->getDefaultChannel()->id,
        'theme_code' => $code,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDays(2),
        'status' => ThemeSchedule::STATUS_PENDING,
    ]);

    delete(route('admin.appearance.themes.delete', $code))->assertOk();

    expect(ThemeSchedule::query()->whereKey($schedule->id)->exists())->toBeFalse();
});

it('should refuse to delete a theme built into the store', function () {
    $this->loginAsAdmin();

    delete(route('admin.appearance.themes.delete', 'default'), [], ['Accept' => 'application/json'])->assertNotFound();
});

// ============================================================================
// Images
// ============================================================================

/**
 * The files of a template whose header shows a hero image.
 */
function uploadFilesWithImage(string $code): array
{
    $files = uploadFiles($code);

    $files['index.html'] = str_replace('<header>', '<header><img src="images/hero.png" alt="Hero Banner" data-bagisto-image="hero">', $files['index.html']);

    return $files + ['images/hero.png' => base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==')];
}

it('should list the images of an uploaded theme', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    $release = Theme::query()->where('code', $code)->value('manifest')['release'];

    get(route('admin.appearance.themes.images.index', $code))
        ->assertOk()
        ->assertSee(trans('admin::app.appearance.themes.images.title'))
        ->assertSee('Hero Banner')
        ->assertSee('themes/'.$code.'/assets/'.$release.'/images/hero.png', false);
});

it('should show a replaced image on the storefront', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    post(route('admin.appearance.themes.images.update', [$code, 'hero']), [
        'image' => UploadedFile::fake()->image('summer.png', 40, 20),
    ])
        ->assertRedirect(route('admin.appearance.themes.images.index', $code))
        ->assertSessionHas('success', trans('admin::app.appearance.themes.images.update-success'));

    $path = Theme::query()->where('code', $code)->value('manifest')['images']['hero'];

    Storage::assertExists($path);

    expect($path)->toStartWith('themes/'.$code.'/images/hero-');

    visitWithTheme($code)
        ->assertOk()
        ->assertSee($path, false)
        ->assertDontSee('/images/hero.png', false)
        ->assertDontSee(HtmlConverter::IMAGE_PLACEHOLDER, false);
});

it('should reset a replaced image to the template\'s own', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    post(route('admin.appearance.themes.images.update', [$code, 'hero']), ['image' => UploadedFile::fake()->image('summer.png')]);

    $path = Theme::query()->where('code', $code)->value('manifest')['images']['hero'];

    delete(route('admin.appearance.themes.images.delete', [$code, 'hero']))
        ->assertRedirect(route('admin.appearance.themes.images.index', $code));

    Storage::assertMissing($path);

    expect(Theme::query()->where('code', $code)->value('manifest')['images'])->toBe([]);

    visitWithTheme($code)
        ->assertOk()
        ->assertSee('/images/hero.png', false);
});

it('should keep a replaced image when the theme is uploaded again', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    post(route('admin.appearance.themes.images.update', [$code, 'hero']), ['image' => UploadedFile::fake()->image('summer.png')]);

    $path = Theme::query()->where('code', $code)->value('manifest')['images']['hero'];

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    expect(Theme::query()->where('code', $code)->value('manifest')['images'])->toBe(['hero' => $path]);

    visitWithTheme($code)
        ->assertOk()
        ->assertSee($path, false);
});

it('should refuse a replacement that is not an image', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    post(route('admin.appearance.themes.images.update', [$code, 'hero']), [
        'image' => UploadedFile::fake()->createWithContent('evil.svg', '<svg onload="alert(1)"></svg>'),
    ])->assertSessionHasErrors('image');

    expect(Theme::query()->where('code', $code)->value('manifest')['images'])->toBe([]);
});

it('should refuse an image the theme does not have', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    post(route('admin.appearance.themes.images.update', [$code, 'missing']), [
        'image' => UploadedFile::fake()->image('summer.png'),
    ])->assertNotFound();
});

it('should show the screenshot an archive ships in the gallery', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code) + ['screenshot.png' => uploadFilesWithImage($code)['images/hero.png']]))->assertOk();

    $theme = Theme::query()->where('code', $code)->sole();

    app(ThemeRegistry::class)->merge();

    expect($theme->manifest['screenshot'])->toBe('screenshot.png')
        ->and(collect(app(ThemeCatalog::class)->all())->firstWhere('code', $code)['screenshot'])
        ->toEndWith('themes/'.$code.'/assets/'.$theme->manifest['release'].'/screenshot.png');
});

it('should fall back to the first image of the page for a theme without a screenshot', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    app(ThemeRegistry::class)->merge();

    expect(Theme::query()->where('code', $code)->value('manifest')['screenshot'])->toBeNull()
        ->and(collect(app(ThemeCatalog::class)->all())->firstWhere('code', $code)['screenshot'])->toEndWith('/images/hero.png');
});

// ============================================================================
// Sections
// ============================================================================

it('should create a section for each block of the home page on every channel', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code, '<section><h2>Welcome Block</h2><img src="images/hero.png"></section><div><h2>Picks</h2><div data-bagisto="products" data-type="featured" data-limit="6"></div></div>') + uploadFilesWithImage($code)))->assertOk();

    $sections = Section::query()->where('theme_code', $code)->orderBy('sort_order')->get();

    $channels = app(ChannelRepository::class)->count();

    expect($sections)->toHaveCount(2 * $channels)
        ->and($sections->where('channel_id', core()->getDefaultChannel()->id)->pluck('type')->values()->all())->toBe(['static_content', 'product_carousel'])
        ->and($sections->firstWhere('type', 'static_content')->name)->toBe('Welcome Block')
        ->and($sections->firstWhere('type', 'static_content')->options['html'])->toContain(ThemeStorage::MEDIA_REFERENCE.'themes/'.$code.'/assets/')
        ->and($sections->firstWhere('type', 'product_carousel')->options)->toEqual(['title' => 'Picks', 'filters' => ['featured' => 1, 'limit' => 6]]);

    visitWithTheme($code)
        ->assertOk()
        ->assertSee('Welcome Block');
});

it('should leave the sections an admin edited alone when the theme is uploaded again', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code, '<section><h2>First</h2></section>')))->assertOk();

    Section::query()->where('theme_code', $code)->update(['name' => 'Edited By Admin']);

    postArchive(uploadArchive(uploadFiles($code, '<section><h2>Second</h2></section><section><h2>Third</h2></section>')))->assertOk();

    expect(Section::query()->where('theme_code', $code)->where('channel_id', core()->getDefaultChannel()->id)->pluck('name')->all())->toBe(['Edited By Admin']);
});

it('should leave the home page\'s images to the section editor once they became sections', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    $files = uploadFilesWithImage($code);

    $files['index.html'] = str_replace('<h1>Template Welcome</h1>', '<section><img src="images/hero.png" alt="Section Photo"></section>', $files['index.html']);

    postArchive(uploadArchive($files))->assertOk();

    get(route('admin.appearance.themes.images.index', $code))
        ->assertOk()
        ->assertSee('Hero Banner')
        ->assertDontSee('Section Photo');
});

it('should offer the images of an uploaded theme only to an admin with the upload permission', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFilesWithImage($code)))->assertOk();

    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes']);

    get(route('admin.appearance.themes.images.index', $code))->assertUnauthorized();
});

// ============================================================================
// Permissions
// ============================================================================

it('should refuse a delete from an admin without the delete permission', function () {
    $this->loginAsAdmin();

    $code = uploadCode();

    postArchive(uploadArchive(uploadFiles($code)))->assertOk();

    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes', 'appearance.themes.upload']);

    delete(route('admin.appearance.themes.delete', $code), [], ['Accept' => 'application/json'])->assertUnauthorized();

    expect(Theme::query()->where('code', $code)->exists())->toBeTrue();
});

it('should refuse an upload from an admin without the upload permission', function () {
    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes']);

    postArchive(uploadArchive(uploadFiles(uploadCode())))->assertUnauthorized();
});

it('should show the upload button only to an admin with the upload permission', function () {
    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes']);

    get(route('admin.appearance.themes.index'))
        ->assertOk()
        ->assertDontSee(trans('admin::app.appearance.themes.upload.btn'));

    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes', 'appearance.themes.upload']);

    get(route('admin.appearance.themes.index'))
        ->assertOk()
        ->assertSee(trans('admin::app.appearance.themes.upload.btn'));
});
