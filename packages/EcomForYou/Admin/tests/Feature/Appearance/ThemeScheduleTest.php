<?php

use EcomForYou\Theme\Models\ThemeSchedule;
use Illuminate\Support\Str;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\postJson;

beforeEach(function () {
    $this->holidayCode = 'holiday-'.Str::lower(Str::random(10));

    config(['themes.shop.'.$this->holidayCode => [
        'name' => 'Holiday Theme',
        'parent' => 'default',
        'assets_path' => 'public/themes/shop/default',
        'views_path' => 'resources/themes/default/views',
    ]]);

    $this->channel = core()->getDefaultChannel();

    $this->channel->theme = 'default';

    $this->channel->save();
});

/**
 * Create a schedule for the default channel straight in the database.
 */
function holidaySchedule(string $code, array $attributes = []): ThemeSchedule
{
    return ThemeSchedule::query()->create($attributes + [
        'channel_id' => core()->getDefaultChannel()->id,
        'theme_code' => $code,
        'starts_at' => now()->addHour(),
        'ends_at' => now()->addDay(),
        'status' => ThemeSchedule::STATUS_PENDING,
    ]);
}

// ============================================================================
// Scheduling
// ============================================================================

it('should schedule a theme switch for a channel', function () {
    $this->loginAsAdmin();

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => $this->holidayCode,
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
    ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.appearance.themes.index'));

    $schedule = ThemeSchedule::query()->where('theme_code', $this->holidayCode)->sole();

    expect($schedule->status)->toBe(ThemeSchedule::STATUS_PENDING)
        ->and($schedule->channel_id)->toBe($this->channel->id)
        ->and($this->channel->fresh()->theme)->toBe('default');
});

it('should switch the channel at once when the schedule has already started', function () {
    $this->loginAsAdmin();

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => $this->holidayCode,
        'starts_at' => now()->subMinute()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ])->assertOk();

    $schedule = ThemeSchedule::query()->where('theme_code', $this->holidayCode)->sole();

    expect($schedule->status)->toBe(ThemeSchedule::STATUS_RUNNING)
        ->and($schedule->previous_theme)->toBe('default')
        ->and($this->channel->fresh()->theme)->toBe($this->holidayCode);
});

it('should refuse a schedule that overlaps another for the same channel', function () {
    $this->loginAsAdmin();

    holidaySchedule($this->holidayCode, [
        'starts_at' => now()->addDays(2),
        'ends_at' => now()->addDays(4),
    ]);

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => $this->holidayCode,
        'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
    ])
        ->assertUnprocessable()
        ->assertJsonPath('message', trans('admin::app.appearance.themes.schedules.overlap'));

    expect(ThemeSchedule::query()->where('theme_code', $this->holidayCode)->count())->toBe(1);
});

it('should refuse a schedule that ends before it starts', function () {
    $this->loginAsAdmin();

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => $this->holidayCode,
        'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('ends_at');
});

it('should refuse a schedule for a theme that is not installed', function () {
    $this->loginAsAdmin();

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => 'not-installed',
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('theme_code');
});

// ============================================================================
// Switching
// ============================================================================

it('should start a due schedule and remember the theme the channel ran', function () {
    $schedule = holidaySchedule($this->holidayCode);

    $this->travel(2)->hours();

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    expect($schedule->fresh()->status)->toBe(ThemeSchedule::STATUS_RUNNING)
        ->and($schedule->fresh()->previous_theme)->toBe('default')
        ->and($this->channel->fresh()->theme)->toBe($this->holidayCode);
});

it('should leave a schedule alone until its start comes', function () {
    $schedule = holidaySchedule($this->holidayCode);

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    expect($schedule->fresh()->status)->toBe(ThemeSchedule::STATUS_PENDING)
        ->and($this->channel->fresh()->theme)->toBe('default');
});

it('should return the channel to its previous theme when the schedule ends', function () {
    $schedule = holidaySchedule($this->holidayCode);

    $this->travel(2)->hours();

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    $this->travel(2)->days();

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    expect($schedule->fresh()->status)->toBe(ThemeSchedule::STATUS_FINISHED)
        ->and($this->channel->fresh()->theme)->toBe('default');
});

it('should keep a theme an admin picked by hand while the schedule ran', function () {
    $schedule = holidaySchedule($this->holidayCode, [
        'starts_at' => now()->subHour(),
        'status' => ThemeSchedule::STATUS_RUNNING,
        'previous_theme' => 'default',
    ]);

    $other = 'other-'.Str::lower(Str::random(10));

    config(['themes.shop.'.$other => ['name' => 'Other', 'parent' => 'default']]);

    $this->channel->theme = $other;

    $this->channel->save();

    $this->travel(2)->days();

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    expect($schedule->fresh()->status)->toBe(ThemeSchedule::STATUS_FINISHED)
        ->and($this->channel->fresh()->theme)->toBe($other);
});

it('should finish a schedule whose whole period passed without switching the channel', function () {
    $schedule = holidaySchedule($this->holidayCode, [
        'starts_at' => now()->subDays(3),
        'ends_at' => now()->subDay(),
    ]);

    $this->artisan('theme:switch-scheduled')->assertSuccessful();

    expect($schedule->fresh()->status)->toBe(ThemeSchedule::STATUS_FINISHED)
        ->and($this->channel->fresh()->theme)->toBe('default');
});

// ============================================================================
// Deleting
// ============================================================================

it('should delete a pending schedule', function () {
    $this->loginAsAdmin();

    $schedule = holidaySchedule($this->holidayCode);

    delete(route('admin.appearance.themes.schedules.delete', $schedule->id))->assertOk();

    expect(ThemeSchedule::query()->whereKey($schedule->id)->exists())->toBeFalse();
});

it('should return the channel to its previous theme when a running schedule is deleted', function () {
    $this->loginAsAdmin();

    $schedule = holidaySchedule($this->holidayCode, [
        'starts_at' => now()->subHour(),
        'status' => ThemeSchedule::STATUS_RUNNING,
        'previous_theme' => 'default',
    ]);

    $this->channel->theme = $this->holidayCode;

    $this->channel->save();

    delete(route('admin.appearance.themes.schedules.delete', $schedule->id))->assertOk();

    expect(ThemeSchedule::query()->whereKey($schedule->id)->exists())->toBeFalse()
        ->and($this->channel->fresh()->theme)->toBe('default');
});

// ============================================================================
// Gallery
// ============================================================================

it('should list the upcoming schedules in the gallery', function () {
    $this->loginAsAdmin();

    holidaySchedule($this->holidayCode);

    get(route('admin.appearance.themes.index'))
        ->assertOk()
        ->assertSee(trans('admin::app.appearance.themes.schedules.title'))
        ->assertSee('Holiday Theme');
});

// ============================================================================
// Permissions
// ============================================================================

it('should refuse a schedule from an admin without the schedule permission', function () {
    $this->loginAsAdminWithPermissions(['appearance', 'appearance.themes']);

    postJson(route('admin.appearance.themes.schedules.store'), [
        'channel_id' => $this->channel->id,
        'theme_code' => $this->holidayCode,
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ])->assertUnauthorized();

    expect(ThemeSchedule::query()->where('theme_code', $this->holidayCode)->exists())->toBeFalse();
});
