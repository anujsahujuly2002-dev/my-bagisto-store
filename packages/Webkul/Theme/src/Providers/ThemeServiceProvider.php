<?php

namespace Webkul\Theme\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Webkul\Theme\AdminThemeResolver;
use Webkul\Theme\Console\Commands\SwitchScheduledThemes;
use Webkul\Theme\Models\SectionProxy;
use Webkul\Theme\Models\ThemeProxy;
use Webkul\Theme\Observers\SectionObserver;
use Webkul\Theme\Observers\ThemeObserver;
use Webkul\Theme\ThemeRegistry;
use Webkul\Theme\ThemeViewFinder;
use Webkul\Theme\Upload\UploadedPage;
use Webkul\Theme\ViewRenderEventManager;

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

        Blade::directive('bagistoVite', function ($expression) {
            return "<?php echo themes()->setBagistoVite({$expression})->toHtml(); ?>";
        });
    }

    /**
     * Whether the stored themes can be read, which needs an installed store or a test run with a migrated database.
     */
    protected function shouldMergeStoredThemes(): bool
    {
        return $this->app->runningUnitTests()
            || file_exists(storage_path('installed'));
    }
}
