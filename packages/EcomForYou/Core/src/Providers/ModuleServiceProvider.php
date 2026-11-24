<?php

namespace EcomForYou\Core\Providers;

use EcomForYou\Core\Models\Channel;
use EcomForYou\Core\Models\CoreConfig;
use EcomForYou\Core\Models\Country;
use EcomForYou\Core\Models\CountryState;
use EcomForYou\Core\Models\CountryStateTranslation;
use EcomForYou\Core\Models\CountryTranslation;
use EcomForYou\Core\Models\Currency;
use EcomForYou\Core\Models\CurrencyExchangeRate;
use EcomForYou\Core\Models\Locale;
use EcomForYou\Core\Models\SubscribersList;

class ModuleServiceProvider extends CoreModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        Channel::class,
        CoreConfig::class,
        Country::class,
        CountryState::class,
        CountryStateTranslation::class,
        CountryTranslation::class,
        Currency::class,
        CurrencyExchangeRate::class,
        Locale::class,
        SubscribersList::class,
    ];
}
