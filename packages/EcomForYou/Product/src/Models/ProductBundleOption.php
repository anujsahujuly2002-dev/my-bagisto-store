<?php

namespace EcomForYou\Product\Models;

use EcomForYou\Core\Eloquent\TranslatableModel;
use EcomForYou\Product\Contracts\ProductBundleOption as ProductBundleOptionContract;
use EcomForYou\Product\Database\Factories\ProductBundleOptionsFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBundleOption extends TranslatableModel implements ProductBundleOptionContract
{
    use HasFactory;

    /**
     * Set timestamp false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Add the translateable attribute.
     *
     * @var array
     */
    public $translatedAttributes = ['label'];

    /**
     * Add fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'is_required',
        'sort_order',
        'product_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Get the bundle option products that owns the bundle option.
     */
    public function bundle_option_products()
    {
        return $this->hasMany(ProductBundleOptionProductProxy::modelClass());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ProductBundleOptionsFactory::new();
    }
}
