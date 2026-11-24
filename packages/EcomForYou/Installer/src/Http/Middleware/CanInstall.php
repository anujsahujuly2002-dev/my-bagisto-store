<?php

namespace EcomForYou\Installer\Http\Middleware;

use Closure;
use EcomForYou\Installer\Helpers\DatabaseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class CanInstall
{
    /**
     * Send an uninstalled store to the installer and keep an installed one out of it. With tenancy on, stores
     * are installed by provisioning and served only while active, so the installer is closed on every host.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('tenancy.enabled')) {
            abort_if($this->isInstallerRequest($request), 404);

            return $next($request);
        }

        if ($this->isAlreadyInstalled()) {
            if ($this->isInstallerRequest($request)) {
                if (! $request->ajax()) {
                    return redirect()->route('shop.home.index');
                }

                return response()->json([
                    'message' => trans('installer::app.installer.middleware.already-installed'),
                ], 403);
            }
        } elseif (! $this->isInstallerRequest($request)) {
            return redirect()->route('installer.index');
        }

        return $next($request);
    }

    /**
     * Whether the single store is installed, recording it once the database shows it is.
     *
     * @return bool
     */
    public function isAlreadyInstalled()
    {
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        if (app(DatabaseManager::class)->isInstalled()) {
            touch(storage_path('installed'));

            Event::dispatch('bagisto.installed');

            return true;
        }

        return false;
    }

    /**
     * Whether the request targets the installer.
     *
     * The comparison is made on the decoded path. The router resolves a percent-encoded path such
     * as `/%69nstall` to the installer, so matching the raw path here would let it slip past the
     * guard on an installed site.
     */
    protected function isInstallerRequest(Request $request): bool
    {
        $path = trim($request->decodedPath(), '/');

        return $path === 'install'
            || Str::startsWith($path, 'install/');
    }
}
