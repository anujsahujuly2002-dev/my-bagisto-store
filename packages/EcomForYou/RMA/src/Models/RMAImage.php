<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMAImage as RMAImageContract;
use Illuminate\Database\Eloquent\Model;

class RMAImage extends Model implements RMAImageContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rma_id',
        'path',
    ];

    /**
     * Get the RMA that owns the image.
     */
    public function rma()
    {
        return $this->belongsTo(RMA::class);
    }
}
