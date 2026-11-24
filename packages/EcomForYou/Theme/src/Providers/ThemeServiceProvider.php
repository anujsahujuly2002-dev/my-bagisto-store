<?php

namespace EcomForYou\Theme\Providers;

use EcomForYou\Tenancy\Events\TenantForgotten;
use EcomForYou\Tenancy\Events\TenantMadeCurrent;
use EcomForYou\Theme\AdminThemeResolver;
use EcomForYou\Theme\Console\Commands\SwitchScheduledThemes;
use EcomForYou\Theme\Listeners\Tenant as TenantListener;
use EcomForYou\Theme\Models\SectionProxy;
use EcomForYou\Theme\Models\ThemeProxy;
use EcomForYou\Theme\Observers\SectionObserver;
use EcomForYou\Theme\Observers\ThemeObserver;
use EcomForYou\Theme\ThemeRegistry;
use EcomForYou\Theme\ThemeViewFinder;
use EcomForYou\Theme\Upload\UploadedPage;
use EcomForYou\Theme\ViewRenderEventManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        include __DIR__.'/../Http/helpers.php';

        $this->app->singleton('view.finder', function ($app) {
            return new ThemeViewFinder(
                $app['files'],
                $app['config']['view.paths'],
                null
            );
        });

        $this->app->singleton(ViewRenderEventManager::class);

        $this->app->singleton(ThemeRegistry::class);

        $this->app->scoped(UploadedPage::class);

        $this->app->scoped(AdminThemeResolver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([SwitchScheduledThemes::class]);
        }

        $this->app->booted(function () {
            if ($this->shouldMergeStoredThemes()) {
                $this->app->make(ThemeRegistry::class)->merge();
            }
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        SectionProxy::observe(SectionObserver::class);

        ThemeProxy::observe(ThemeObserver::class);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('theme:switch-scheduled')
                ->everyMinute()
                ->withoutOverlapping();
        });

        Event::listen(TenantMadeCurrent::class, [TenantListener::class, 'madeCurrent']);

        Event::listen(TenantForgotten::class, [TenantListener::class, 'forgotten']);

        Blade::directive('bagistoVite', function ($expression) {
            return "<?php echo themes()->setBagistoVite({$expression})->toHtml(); ?>";
        });
    }

    /**
     * Whether the single store's themes can be read at boot, which needs an installed store or a test run with a
     * migrated database. With tenancy on, a store's themes are read only once its tenant is current.
     */
    protected function shouldMergeStoredThemes(): bool
    {
        if (config('tenancy.enabled')) {
            return false;
        }

        return $this->app->runningUnitTests()
            || file_exists(storage_path('installed'));
    }
}
