<?php

namespace EcomForYou\Product\Models;

use EcomForYou\Product\Contracts\ProductBundleOptionProduct as ProductBundleOptionProductContract;
use EcomForYou\Product\Database\Factories\ProductBundleOptionProductFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundleOptionProduct extends Model implements ProductBundleOptionProductContract
{
    use HasFactory;

    /**
     * Set timestamp false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable property of the model.
     *
     * @var array
     */
    protected $fillable = [
        'qty',
        'is_user_defined',
        'sort_order',
        'is_default',
        'product_bundle_option_id',
        'product_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_user_defined' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the bundle option that owns this resource.
     */
    public function bundle_option()
    {
        return $this->belongsTo(ProductBundleOptionProxy::modelClass(), 'product_bundle_option_id');
    }

    /**
     * Get the product that owns the image.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return ProductBundleOptionProductFactory::new();
    }
}
