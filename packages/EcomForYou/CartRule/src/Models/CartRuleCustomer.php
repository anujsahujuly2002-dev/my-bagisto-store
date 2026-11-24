<?php

namespace EcomForYou\CartRule\Models;

use EcomForYou\CartRule\Contracts\CartRuleCustomer as CartRuleCustomerContract;
use Illuminate\Database\Eloquent\Model;

class CartRuleCustomer extends Model implements CartRuleCustomerContract
{
    public $timestamps = false;

    protected $fillable = [
        'times_used',
        'cart_rule_id',
        'customer_id',
    ];
}
