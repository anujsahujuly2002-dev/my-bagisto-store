<?php

namespace EcomForYou\Core\Models;

use EcomForYou\Core\Contracts\Country as CountryContract;
use EcomForYou\Core\Eloquent\TranslatableModel;

class Country extends TranslatableModel implements CountryContract
{
    public $timestamps = false;

    public $translatedAttributes = ['name'];

    protected $with = ['translations'];

    /**
     * Get the States.
     */
    public function states()
    {
        return $this->hasMany(CountryStateProxy::modelClass());
    }
}
