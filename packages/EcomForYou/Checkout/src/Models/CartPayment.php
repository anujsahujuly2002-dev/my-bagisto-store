<?php

namespace EcomForYou\Checkout\Models;

use EcomForYou\Checkout\Contracts\CartPayment as CartPaymentContract;
use EcomForYou\Checkout\Database\Factories\CartPaymentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartPayment extends Model implements CartPaymentContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cart_payment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'method',
        'method_title',
        'cart_id',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return CartPaymentFactory::new();
    }
}
