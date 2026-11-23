<?php

namespace Webkul\Theme\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Theme\ThemeScheduler;

class SwitchScheduledThemes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:switch-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start and end the scheduled storefront theme switches whose time has come.';

    /**
     * Execute the console command.
     */
    public function handle(ThemeScheduler $themeScheduler): int
    {
        $result = $themeScheduler->run(now());

        $this->info("Started {$result['started']} and ended {$result['ended']} scheduled theme switches.");

        return self::SUCCESS;
    }
}
