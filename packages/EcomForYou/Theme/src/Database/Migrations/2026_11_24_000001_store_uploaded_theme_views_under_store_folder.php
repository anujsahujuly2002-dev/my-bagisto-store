<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The source of a theme registered from an uploaded archive.
     */
    protected const SOURCE_UPLOAD = 'upload';

    /**
     * Store each uploaded theme's views path relative to the store's own folder instead of the application root.
     */
    public function up(): void
    {
        $prefix = $this->storeFolderFromBase().'/';

        $this->rewrite(fn (string $path) => Str::startsWith($path, $prefix)
            ? Str::after($path, $prefix)
            : null);
    }

    /**
     * Store each uploaded theme's views path relative to the application root again.
     */
    public function down(): void
    {
        $prefix = $this->storeFolderFromBase().'/';

        $this->rewrite(fn (string $path) => Str::startsWith($path, $prefix)
            ? null
            : $prefix.$path);
    }

    /**
     * Apply a rewrite to every uploaded theme's views path, leaving a path the rewrite returns null for as it is.
     */
    protected function rewrite(callable $rewrite): void
    {
        DB::table('themes')
            ->where('source', self::SOURCE_UPLOAD)
            ->whereNotNull('views_path')
            ->orderBy('id')
            ->get(['id', 'views_path'])
            ->each(function ($theme) use ($rewrite) {
                $path = $rewrite($theme->views_path);

                if (! is_null($path)) {
                    DB::table('themes')->where('id', $theme->id)->update(['views_path' => $path]);
                }
            });
    }

    /**
     * The single store's folder for local files, relative to the application root.
     */
    protected function storeFolderFromBase(): string
    {
        return trim(Str::after(storage_path('app'), base_path()), '/\\');
    }
};
