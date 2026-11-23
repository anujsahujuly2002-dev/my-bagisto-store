<?php

namespace Webkul\Theme;

use Carbon\CarbonInterface;
use Webkul\Theme\Contracts\ThemeSchedule;
use Webkul\Theme\Models\ThemeSchedule as ThemeScheduleModel;
use Webkul\Theme\Repositories\ThemeScheduleRepository;

class ThemeScheduler
{
    /**
     * Create a new scheduler instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeScheduleRepository $themeScheduleRepository,
        protected ThemeActivator $themeActivator
    ) {}

    /**
     * End the schedules whose time is up, then start the ones whose time has come, returning how many of each.
     *
     * @return array{started: int, ended: int}
     */
    public function run(CarbonInterface $now): array
    {
        $ended = 0;

        foreach ($this->themeScheduleRepository->dueToEnd($now) as $schedule) {
            $this->end($schedule);

            $ended++;
        }

        $started = 0;

        foreach ($this->themeScheduleRepository->dueToStart($now) as $schedule) {
            if ($this->start($schedule, $now)) {
                $started++;
            }
        }

        return compact('started', 'ended');
    }

    /**
     * Switch the channel to the scheduled theme, remembering the one it ran, unless the schedule has already
     * passed or its theme is gone.
     *
     * @param  ThemeSchedule  $schedule
     */
    public function start($schedule, CarbonInterface $now): bool
    {
        if (
            $schedule->ends_at->lessThanOrEqualTo($now)
            || ! is_array(config('themes.shop.'.$schedule->theme_code))
        ) {
            $this->themeScheduleRepository->update(['status' => ThemeScheduleModel::STATUS_FINISHED], $schedule->id);

            return false;
        }

        $channel = $schedule->channel;

        $this->themeScheduleRepository->update([
            'status' => ThemeScheduleModel::STATUS_RUNNING,
            'previous_theme' => $channel->theme,
        ], $schedule->id);

        if ($channel->theme !== $schedule->theme_code) {
            $this->themeActivator->activate($channel, $schedule->theme_code);
        }

        return true;
    }

    /**
     * Return the channel to the theme it ran before the schedule, unless it has been switched by hand since.
     *
     * @param  ThemeSchedule  $schedule
     */
    public function end($schedule): void
    {
        $channel = $schedule->channel;

        $this->themeScheduleRepository->update(['status' => ThemeScheduleModel::STATUS_FINISHED], $schedule->id);

        if (
            $channel->theme === $schedule->theme_code
            && filled($schedule->previous_theme)
            && $schedule->previous_theme !== $schedule->theme_code
            && is_array(config('themes.shop.'.$schedule->previous_theme))
        ) {
            $this->themeActivator->activate($channel, $schedule->previous_theme);
        }
    }
}
