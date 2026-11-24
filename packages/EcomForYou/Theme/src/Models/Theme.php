<?php

namespace EcomForYou\Theme\Models;

use EcomForYou\Theme\Contracts\Theme as ThemeContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Theme extends Model implements ThemeContract
{
    /**
     * The panel of a storefront theme.
     */
    public const PANEL_SHOP = 'shop';

    /**
     * The panel of an admin theme.
     */
    public const PANEL_ADMIN = 'admin';

    /**
     * The source of a theme registered from an uploaded archive.
     */
    public const SOURCE_UPLOAD = 'upload';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'themes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'panel',
        'name',
        'version',
        'parent',
        'assets_path',
        'views_path',
        'vite',
        'manifest',
        'source',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'vite' => 'array',
        'manifest' => 'array',
        'status' => 'boolean',
    ];

    /**
     * Return the theme as the entry `config/themes.php` registers for its panel.
     */
    public function toConfig(): array
    {
        return array_filter([
            'name' => $this->name,
            'parent' => $this->parent,
            'assets_path' => $this->assets_path,
            'views_path' => $this->viewsPathFromBase(),
            'vite' => $this->vite,
            'images' => $this->manifest['images'] ?? null,
        ], fn ($value) => ! is_null($value));
    }

    /**
     * The views path relative to the application root, where every reader resolves it. An uploaded theme stores
     * its path under the store's own folder, so it moves with the store and is never shared between stores.
     */
    public function viewsPathFromBase(): ?string
    {
        if (
            $this->source !== self::SOURCE_UPLOAD
            || blank($this->views_path)
        ) {
            return $this->views_path;
        }

        return ltrim(Str::after(tenant_storage_path($this->views_path), base_path()), '/\\');
    }
}
