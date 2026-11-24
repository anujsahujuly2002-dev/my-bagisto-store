<?php

namespace EcomForYou\Attribute\Models;

use EcomForYou\Attribute\Contracts\AttributeTranslation as AttributeTranslationContract;
use Illuminate\Database\Eloquent\Model;

class AttributeTranslation extends Model implements AttributeTranslationContract
{
    public $timestamps = false;

    protected $fillable = ['name'];
}
