<?php

namespace EcomForYou\CatalogRule\Models;

use EcomForYou\CatalogRule\Contracts\CatalogRuleProductPrice as CatalogRuleProductPriceContract;
use Illuminate\Database\Eloquent\Model;

class CatalogRuleProductPrice extends Model implements CatalogRuleProductPriceContract
{
    public $timestamps = false;

    protected $fillable = [
        'price',
        'rule_date',
        'starts_from',
        'ends_till',
        'catalog_rule_id',
        'channel_id',
        'customer_group_id',
    ];
}
