<?php

namespace EcomForYou\Omnibus\Models;

use EcomForYou\Omnibus\Contracts\OmnibusPrice as OmnibusPriceContract;
use EcomForYou\Product\Models\ProductProxy;
use Illuminate\Database\Eloquent\Model;

class OmnibusPrice extends Model implements OmnibusPriceContract
{
    /**
     * Table.
     *
     * @var string
     */
    protected $table = 'product_omnibus_prices';

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'channel_id',
        'currency_code',
        'price',
        'recorded_at',
    ];

    /**
     * Casts.
     *
     * @var array
     */
    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the product that the snapshot belongs to.
     */
    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }
}
