<?php

namespace EcomForYou\BookingProduct\Models;

use EcomForYou\BookingProduct\Contracts\BookingProductAppointmentSlot as BookingProductAppointmentSlotContract;
use Illuminate\Database\Eloquent\Model;

class BookingProductAppointmentSlot extends Model implements BookingProductAppointmentSlotContract
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
        'duration',
        'break_time',
        'same_slot_all_days',
        'slots',
        'allow_slot_overlap',
        'booking_product_id',
    ];
}
