<?php

namespace Webkul\Theme\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Theme\Contracts\Theme as ThemeContract;

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
            'views_path' => $this->views_path,
            'vite' => $this->vite,
            'images' => $this->manifest['images'] ?? null,
        ], fn ($value) => ! is_null($value));
    }
}
