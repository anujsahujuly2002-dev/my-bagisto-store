<?php

namespace EcomForYou\Product\Models;

use EcomForYou\Product\Contracts\ProductDownloadableSampleTranslation as ProductDownloadableSampleTranslationContract;
use Illuminate\Database\Eloquent\Model;

class ProductDownloadableSampleTranslation extends Model implements ProductDownloadableSampleTranslationContract
{
    public $timestamps = false;

    protected $fillable = ['title'];
}
