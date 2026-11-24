<?php

namespace EcomForYou\Admin\Http\Controllers;

use EcomForYou\Core\Enums\SupportedFilesystemEnum;
use EcomForYou\Core\Filesystem\StorageSynchronizer;
use EcomForYou\Core\Jobs\SyncStorage;
use Illuminate\Http\JsonResponse;

class StorageSyncController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected StorageSynchronizer $synchronizer) {}

    /**
     * How many files the configured disk is still missing.
     */
    public function status(): JsonResponse
    {
        $driver = SupportedFilesystemEnum::currentDriver();

        if (! $driver->supportsSync()) {
            return new JsonResponse([
                'pending' => 0,
                'local' => true,
            ]);
        }

        try {
            $pending = $this->synchronizer->pending(SupportedFilesystemEnum::default()->value, $driver->value);
        } catch (\Throwable $e) {
            report($e);

            return new JsonResponse([
                'pending' => 0,
                'local' => false,
                'unreachable' => true,
            ]);
        }

        return new JsonResponse([
            'pending' => $pending,
            'local' => false,
            'unreachable' => false,
        ]);
    }

    /**
     * Copy whatever the configured disk is missing, in the background.
     */
    public function sync(): JsonResponse
    {
        $driver = SupportedFilesystemEnum::currentDriver();

        if (! $driver->supportsSync()) {
            return new JsonResponse([
                'message' => trans('admin::app.configuration.index.file-management.general.settings.sync.local'),
            ], 422);
        }

        SyncStorage::dispatch(SupportedFilesystemEnum::default()->value, $driver->value);

        return new JsonResponse([
            'message' => trans('admin::app.configuration.index.file-management.general.settings.sync.started'),
        ]);
    }
}
