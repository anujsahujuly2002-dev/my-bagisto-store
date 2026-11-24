<?php

namespace EcomForYou\Theme\Repositories;

use Carbon\CarbonInterface;
use EcomForYou\Core\Eloquent\Repository;
use EcomForYou\Theme\Contracts\ThemeSchedule;
use EcomForYou\Theme\Models\ThemeSchedule as ThemeScheduleModel;
use Illuminate\Support\Collection;

class ThemeScheduleRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ThemeSchedule::class;
    }

    /**
     * Return the schedules that have not finished, soonest first, with their channels.
     */
    public function upcoming(): Collection
    {
        return $this->model
            ->newQuery()
            ->with('channel')
            ->whereIn('status', [ThemeScheduleModel::STATUS_PENDING, ThemeScheduleModel::STATUS_RUNNING])
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Return the pending schedules whose start has come.
     */
    public function dueToStart(CarbonInterface $now): Collection
    {
        return $this->model
            ->newQuery()
            ->with('channel')
            ->where('status', ThemeScheduleModel::STATUS_PENDING)
            ->where('starts_at', '<=', $now)
            ->orderBy('starts_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Return the running schedules whose end has come.
     */
    public function dueToEnd(CarbonInterface $now): Collection
    {
        return $this->model
            ->newQuery()
            ->with('channel')
            ->where('status', ThemeScheduleModel::STATUS_RUNNING)
            ->where('ends_at', '<=', $now)
            ->orderBy('ends_at')
            ->orderBy('id')
            ->get();
    }

    /**
     * Whether a schedule that has not finished switches the channel during any part of the period.
     */
    public function overlaps(int $channelId, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        return $this->model
            ->newQuery()
            ->where('channel_id', $channelId)
            ->whereIn('status', [ThemeScheduleModel::STATUS_PENDING, ThemeScheduleModel::STATUS_RUNNING])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }

    /**
     * Remove every schedule of a theme that has not started.
     */
    public function deletePendingFor(string $themeCode): void
    {
        $this->model
            ->newQuery()
            ->where('theme_code', $themeCode)
            ->where('status', ThemeScheduleModel::STATUS_PENDING)
            ->delete();
    }
}
