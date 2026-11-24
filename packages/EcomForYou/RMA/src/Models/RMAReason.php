<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMAReason as RMAReasonContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RMAReason extends Model implements RMAReasonContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_reasons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'status',
        'position',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'position' => 'integer',
        'is_admin' => 'boolean',
    ];

    /**
     * Define has many relationship with rma resolution.
     */
    public function reasonResolutions(): HasMany
    {
        return $this->hasMany(RMAReasonResolution::class, 'rma_reason_id');
    }
}
