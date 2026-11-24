<?php

namespace EcomForYou\Attribute\Models;

use EcomForYou\Attribute\Contracts\AttributeGroupTranslation as AttributeGroupTranslationContract;
use Illuminate\Database\Eloquent\Model;

class AttributeGroupTranslation extends Model implements AttributeGroupTranslationContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
