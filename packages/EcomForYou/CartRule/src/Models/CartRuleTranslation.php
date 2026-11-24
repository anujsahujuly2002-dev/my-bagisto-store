<?php

namespace EcomForYou\CartRule\Models;

use EcomForYou\CartRule\Contracts\CartRuleTranslation as CartRuleTranslationContract;
use Illuminate\Database\Eloquent\Model;

class CartRuleTranslation extends Model implements CartRuleTranslationContract
{
    public $timestamps = false;

    protected $fillable = ['label'];
}
