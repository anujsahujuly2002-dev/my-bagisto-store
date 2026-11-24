<?php

namespace EcomForYou\BookingProduct\Models;

use EcomForYou\BookingProduct\Contracts\BookingProductEventTicketTranslation as BookingProductEventTicketTranslationContract;
use Illuminate\Database\Eloquent\Model;

class BookingProductEventTicketTranslation extends Model implements BookingProductEventTicketTranslationContract
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'description',
    ];
}
