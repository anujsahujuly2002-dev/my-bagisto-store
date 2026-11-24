<?php

namespace EcomForYou\Sales\Models;

use EcomForYou\Sales\Contracts\OrderPayment as OrderPaymentContract;
use EcomForYou\Sales\Database\Factories\OrderPaymentFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model implements OrderPaymentContract
{
    use HasFactory;

    protected $table = 'order_payment';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'additional' => 'array',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return OrderPaymentFactory::new();
    }
}
