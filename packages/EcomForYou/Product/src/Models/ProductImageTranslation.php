<?php

namespace EcomForYou\Product\Models;

use EcomForYou\Product\Contracts\ProductImageTranslation as ProductImageTranslationContract;
use Illuminate\Database\Eloquent\Model;

class ProductImageTranslation extends Model implements ProductImageTranslationContract
{
    /**
     * Timestamp.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = ['alt_text'];
}
