<?php

use EcomForYou\Shipping\Carriers\FlatRate;
use EcomForYou\Shipping\Carriers\Free;

return [
    'flatrate' => [
        'code' => 'flatrate',
        'title' => 'Flat Rate',
        'description' => 'Flat Rate Shipping',
        'active' => true,
        'default_rate' => '10',
        'type' => 'per_unit',
        'class' => FlatRate::class,
    ],

    'free' => [
        'code' => 'free',
        'title' => 'Free Shipping',
        'description' => 'Free Shipping',
        'active' => true,
        'default_rate' => '0',
        'class' => Free::class,
    ],
];
