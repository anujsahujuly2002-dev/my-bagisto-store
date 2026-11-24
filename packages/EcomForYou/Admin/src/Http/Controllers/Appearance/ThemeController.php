<?php

namespace EcomForYou\Admin\Http\Controllers\Appearance;

use Carbon\Carbon;
use EcomForYou\Admin\Http\Controllers\Controller;
use EcomForYou\Core\Repositories\ChannelRepository;
use EcomForYou\Theme\Exceptions\InvalidThemeArchive;
use EcomForYou\Theme\Jobs\InstallUploadedTheme;
use EcomForYou\Theme\Models\Theme as ThemeModel;
use EcomForYou\Theme\Models\ThemeSchedule;
use EcomForYou\Theme\Repositories\ThemeRepository;
use EcomForYou\Theme\Repositories\ThemeScheduleRepository;
use EcomForYou\Theme\ThemeActivator;
use EcomForYou\Theme\ThemeCatalog;
use EcomForYou\Theme\ThemePreview;
use EcomForYou\Theme\ThemeScheduler;
use EcomForYou\Theme\Upload\ThemeImages;
use EcomForYou\Theme\Upload\ThemeInstaller;
use EcomForYou\Theme\Upload\ThemeUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ThemeController extends Controller
{
    /**
     * The largest theme archive accepted, in kilobytes.
     */
    public const MAX_UPLOAD_KILOBYTES = 51200;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ThemeCatalog $themeCatalog,
        protected ChannelRepository $channelRepository,
        protected ThemeInstaller $themeInstaller,
        protected ThemePreview $themePreview,
        protected ThemeUploads $themeUploads,
        protected ThemeRepository $themeRepository,
        protected ThemeScheduleRepository $themeScheduleRepository,
        protected ThemeActivator $themeActivator,
        protected ThemeScheduler $themeScheduler,
        protected ThemeImages $themeImages
    ) {}

    /**
     * Display the theme gallery, listing only the themes installed on this store.
     *
     * @return View
     */
    public function index()
    {
        $themes = $this->themeCatalog->all()->where('is_installed', true)->values();

        $channels = $this->channelRepository->all();

        $schedules = $this->themeScheduleRepository->upcoming()->map(fn ($schedule) => [
            'id' => $schedule->id,
            'channel' => $schedule->channel?->name,
            'theme' => $themes->firstWhere('code', $schedule->theme_code)['name'] ?? $schedule->theme_code,
            'starts_at' => $schedule->starts_at->format('Y-m-d H:i'),
            'ends_at' => $schedule->ends_at->format('Y-m-d H:i'),
            'status' => $schedule->status,
        ])->values();

        return view('admin::appearance.themes.index', compact('themes', 'channels', 'schedules'));
    }

    /**
     * Report what switching the given channels over to this theme would leave behind.
     *
     * @return JsonResponse
     */
    public function impact(string $code)
    {
        $this->validate(request(), $this->channelRules());

        $themes = $this->themeCatalog->all()->keyBy('code');

        $impact = $this->channelRepository
            ->findWhereIn('id', request()->input('channel_ids'))
            ->map(fn ($channel) => [
                'channel_id' => $channel->id,
                'channel' => $channel->name,
                'current_theme' => $channel->theme ? ($themes->get($channel->theme)['name'] ?? $channel->theme) : null,
                'customizations' => $channel->theme && $channel->theme !== $code
                    ? $this->themeCatalog->sectionCount($channel->id, $channel->theme)
                    : 0,
            ])
            ->filter(fn ($row) => $row['customizations'] > 0)
            ->values();

        return new JsonResponse(['impact' => $impact]);
    }

    /**
     * Activate a theme on one or more channels.
     *
     * @return JsonResponse
     */
    public function activate(string $code)
    {
        $this->validate(request(), $this->channelRules());

        if (! $this->themeCatalog->isInstalled($code)) {
            return new JsonResponse([
                'message' => trans('admin::app.appearance.themes.index.not-installed'),
            ], 404);
        }

        $theme = $this->themeCatalog->find($code);

        $channels = $this->channelRepository->findWhereIn('id', request()->input('channel_ids'));

        foreach ($channels as $channel) {
            $this->themeActivator->activate($channel, $code);
        }

        session()->flash('success', trans('admin::app.appearance.themes.index.activate-success', [
            'theme' => $theme['name'],
            'channel' => $channels->pluck('name')->implode(', '),
        ]));

        return new JsonResponse([
            'redirect_url' => route('admin.appearance.themes.index'),
        ]);
    }

    /**
     * Check an uploaded HTML template archive and queue it to be installed as a storefront theme.
     *
     * @return JsonResponse
     */
    public function upload()
    {
        $this->validate(request(), [
            'archive' => 'required|file|mimes:zip|max:'.self::MAX_UPLOAD_KILOBYTES,
        ]);

        $archive = request()->file('archive');

        try {
            $code = $this->themeInstaller->prepare($archive->getRealPath(), $archive->getClientOriginalName());
        } catch (InvalidThemeArchive $exception) {
            return $this->uploadRefused($exception->reason, $exception->replace);
        }

        $id = (string) Str::uuid();

        $path = $archive->move($this->themeUploads->directory(), $id.'.zip')->getPathname();

        $adminId = auth()->guard('admin')->id();

        $this->themeUploads->queue($id, $code, $adminId);

        try {
            InstallUploadedTheme::dispatch($id, $path, $archive->getClientOriginalName(), $adminId);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $this->uploadResponse($id);
    }

    /**
     * Report how a queued upload is going, for the gallery to poll until it settles.
     *
     * @return JsonResponse
     */
    public function uploadStatus(string $id)
    {
        $status = $this->themeUploads->find($id);

        abort_if(
            empty($status)
            || ($status['admin_id'] ?? null) !== auth()->guard('admin')->id(),
            404
        );

        return $this->uploadResponse($id);
    }

    /**
     * Roll an uploaded theme back to the release before its current one.
     *
     * @return JsonResponse
     */
    public function rollback(string $code)
    {
        $theme = $this->findUploaded($code);

        if (! $this->themeInstaller->canRollback($theme)) {
            return new JsonResponse([
                'message' => trans('admin::app.appearance.themes.manage.rollback-unavailable'),
            ], 422);
        }

        $theme = $this->themeInstaller->rollback($theme);

        session()->flash('success', trans('admin::app.appearance.themes.manage.rollback-success', ['theme' => $theme->name]));

        return new JsonResponse([
            'redirect_url' => route('admin.appearance.themes.index'),
        ]);
    }

    /**
     * Delete an uploaded theme that no channel runs, with its files and the switches scheduled to it.
     *
     * @return JsonResponse
     */
    public function destroy(string $code)
    {
        $theme = $this->findUploaded($code);

        $channels = $this->themeCatalog->activeChannels($code);

        if ($channels->isNotEmpty()) {
            return new JsonResponse([
                'message' => trans('admin::app.appearance.themes.manage.delete-in-use', [
                    'channels' => $channels->pluck('name')->implode(', '),
                ]),
            ], 422);
        }

        $this->themeScheduleRepository->deletePendingFor($code);

        $this->themeInstaller->uninstall($theme);

        session()->flash('success', trans('admin::app.appearance.themes.manage.delete-success', ['theme' => $theme->name]));

        return new JsonResponse([
            'redirect_url' => route('admin.appearance.themes.index'),
        ]);
    }

    /**
     * Schedule a channel to switch to a theme between two dates and back again after.
     *
     * @return JsonResponse
     */
    public function storeSchedule()
    {
        $this->validate(request(), [
            'channel_id' => 'required|in:'.implode(',', $this->channelRepository->pluck('id')->toArray()),
            'theme_code' => 'required|in:'.implode(',', array_keys(config('themes.shop', []))),
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at|after:now',
        ]);

        $startsAt = Carbon::parse(request()->input('starts_at'));

        $endsAt = Carbon::parse(request()->input('ends_at'));

        if ($this->themeScheduleRepository->overlaps((int) request()->input('channel_id'), $startsAt, $endsAt)) {
            $message = trans('admin::app.appearance.themes.schedules.overlap');

            return new JsonResponse([
                'message' => $message,
                'errors' => ['starts_at' => [$message]],
            ], 422);
        }

        $schedule = $this->themeScheduleRepository->create([
            'channel_id' => request()->input('channel_id'),
            'theme_code' => request()->input('theme_code'),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => ThemeSchedule::STATUS_PENDING,
        ]);

        if ($startsAt->lessThanOrEqualTo(now())) {
            $this->themeScheduler->start($schedule, now());
        }

        session()->flash('success', trans('admin::app.appearance.themes.schedules.create-success'));

        return new JsonResponse([
            'redirect_url' => route('admin.appearance.themes.index'),
        ]);
    }

    /**
     * Delete a scheduled switch, returning the channel to its previous theme when the switch is running.
     *
     * @return JsonResponse
     */
    public function destroySchedule(int $id)
    {
        $schedule = $this->themeScheduleRepository->findOrFail($id);

        if ($schedule->status === ThemeSchedule::STATUS_RUNNING) {
            $this->themeScheduler->end($schedule);
        }

        $this->themeScheduleRepository->delete($id);

        session()->flash('success', trans('admin::app.appearance.themes.schedules.delete-success'));

        return new JsonResponse([
            'redirect_url' => route('admin.appearance.themes.index'),
        ]);
    }

    /**
     * List the images of an uploaded theme an admin can replace.
     *
     * @return View
     */
    public function images(string $code)
    {
        $theme = $this->findUploaded($code);

        $images = $this->themeImages->all($theme);

        return view('admin::appearance.themes.images', compact('theme', 'images'));
    }

    /**
     * Replace one of an uploaded theme's images with an uploaded file.
     *
     * @return RedirectResponse
     */
    public function updateImage(string $code, string $image)
    {
        $theme = $this->findUploaded($code);

        abort_unless($this->themeImages->exists($theme, $image), 404);

        $this->validate(request(), [
            'image' => 'required|file|mimes:'.implode(',', ThemeImages::MIMES).'|max:'.ThemeImages::MAX_KILOBYTES,
        ]);

        $this->themeImages->replace($theme, $image, request()->file('image'));

        session()->flash('success', trans('admin::app.appearance.themes.images.update-success'));

        return redirect()->route('admin.appearance.themes.images.index', $code);
    }

    /**
     * Return one of an uploaded theme's images to the file its template ships.
     *
     * @return RedirectResponse
     */
    public function destroyImage(string $code, string $image)
    {
        $theme = $this->findUploaded($code);

        abort_unless($this->themeImages->exists($theme, $image), 404);

        $this->themeImages->reset($theme, $image);

        session()->flash('success', trans('admin::app.appearance.themes.images.reset-success'));

        return redirect()->route('admin.appearance.themes.images.index', $code);
    }

    /**
     * Start previewing an installed theme across the storefront for this admin's session alone.
     *
     * @return RedirectResponse
     */
    public function preview(string $code)
    {
        abort_unless($this->themeCatalog->isInstalled($code), 404);

        $this->themePreview->start($code);

        return redirect()->route('shop.home.index');
    }

    /**
     * Stop previewing, returning the storefront to the theme its channel runs.
     *
     * @return RedirectResponse
     */
    public function stopPreview()
    {
        $this->themePreview->stop();

        return redirect()->route('shop.home.index');
    }

    /**
     * Answer for a queued upload: where to go once it is installed, why it failed, or where to ask again.
     */
    protected function uploadResponse(string $id): JsonResponse
    {
        $status = $this->themeUploads->find($id);

        if (($status['status'] ?? null) === ThemeUploads::STATUS_INSTALLED) {
            session()->flash('success', trans('admin::app.appearance.themes.upload.success', ['theme' => $status['name']]));

            return new JsonResponse([
                'status' => ThemeUploads::STATUS_INSTALLED,
                'redirect_url' => route('admin.appearance.themes.index'),
            ]);
        }

        if (($status['status'] ?? null) === ThemeUploads::STATUS_FAILED) {
            return $this->uploadRefused($status['reason'], $status['replace'] ?? []);
        }

        return new JsonResponse([
            'status' => ThemeUploads::STATUS_QUEUED,
            'message' => trans('admin::app.appearance.themes.upload.processing'),
            'status_url' => route('admin.appearance.themes.upload_status', $id),
        ], 202);
    }

    /**
     * Refuse an upload with the message its reason names, against the archive field.
     */
    protected function uploadRefused(string $reason, array $replace = []): JsonResponse
    {
        $message = trans('admin::app.appearance.themes.upload.errors.'.$reason, $replace);

        return new JsonResponse([
            'status' => ThemeUploads::STATUS_FAILED,
            'message' => $message,
            'errors' => ['archive' => [$message]],
        ], 422);
    }

    /**
     * Find the uploaded storefront theme of the code, or answer with a 404.
     *
     * @return ThemeModel
     */
    protected function findUploaded(string $code)
    {
        $theme = $this->themeRepository->findOneWhere([
            'code' => $code,
            'panel' => ThemeModel::PANEL_SHOP,
            'source' => ThemeModel::SOURCE_UPLOAD,
        ]);

        abort_if(is_null($theme), 404);

        return $theme;
    }

    /**
     * Validation rules for the channels a theme is being applied to.
     */
    protected function channelRules(): array
    {
        return [
            'channel_ids' => 'required|array|min:1',
            'channel_ids.*' => 'required|in:'.implode(',', $this->channelRepository->pluck('id')->toArray()),
        ];
    }
}
