<?php

namespace EcomForYou\RMA\Models;

use EcomForYou\RMA\Contracts\RMACustomField as RMACustomFieldContracts;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RMACustomField extends Model implements RMACustomFieldContracts
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rma_custom_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'code',
        'label',
        'type',
        'is_required',
        'position',
        'input_validation',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'boolean',
        'is_required' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Get the options for the custom field.
     */
    public function options(): HasMany
    {
        return $this->hasMany(RMACustomFieldOption::class, 'rma_custom_field_id');
    }
}
