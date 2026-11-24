<?php

namespace EcomForYou\CartRule\Models;

use EcomForYou\CartRule\Contracts\CartRuleCouponUsage as CartRuleCouponUsageContract;
use Illuminate\Database\Eloquent\Model;

class CartRuleCouponUsage extends Model implements CartRuleCouponUsageContract
{
    public $timestamps = false;

    protected $table = 'cart_rule_coupon_usage';

    protected $guarded = [
        'created_at',
        'updated_at',
    ];
}
