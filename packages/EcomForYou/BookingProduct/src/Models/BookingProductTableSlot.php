<?php

namespace EcomForYou\BookingProduct\Models;

use EcomForYou\BookingProduct\Contracts\BookingProductTableSlot as BookingProductTableSlotContract;
use Illuminate\Database\Eloquent\Model;

class BookingProductTableSlot extends Model implements BookingProductTableSlotContract
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'same_slot_all_days' => 'boolean',
        'slots' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'price_type',
        'guest_limit',
        'duration',
        'break_time',
        'prevent_scheduling_before',
        'same_slot_all_days',
        'slots',
        'allow_slot_overlap',
        'booking_product_id',
    ];
}
