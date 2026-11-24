<?php

namespace EcomForYou\Attribute\Models;

use EcomForYou\Attribute\Contracts\AttributeOptionTranslation as AttributeOptionTranslationContract;
use Illuminate\Database\Eloquent\Model;

class AttributeOptionTranslation extends Model implements AttributeOptionTranslationContract
{
    public $timestamps = false;

    protected $fillable = ['label', 'swatch_alt'];
}
