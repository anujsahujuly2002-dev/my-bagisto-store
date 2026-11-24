<?php

use EcomForYou\Product\Type\Booking;
use EcomForYou\Product\Type\Bundle;
use EcomForYou\Product\Type\Configurable;
use EcomForYou\Product\Type\Downloadable;
use EcomForYou\Product\Type\Grouped;
use EcomForYou\Product\Type\Simple;
use EcomForYou\Product\Type\Virtual;

return [
    'simple' => [
        'key' => 'simple',
        'name' => 'product::app.type.simple',
        'class' => Simple::class,
        'sort' => 1,
    ],

    'configurable' => [
        'key' => 'configurable',
        'name' => 'product::app.type.configurable',
        'class' => Configurable::class,
        'sort' => 2,
    ],

    'grouped' => [
        'key' => 'grouped',
        'name' => 'product::app.type.grouped',
        'class' => Grouped::class,
        'sort' => 3,
    ],

    'bundle' => [
        'key' => 'bundle',
        'name' => 'product::app.type.bundle',
        'class' => Bundle::class,
        'sort' => 4,
    ],

    'downloadable' => [
        'key' => 'downloadable',
        'name' => 'product::app.type.downloadable',
        'class' => Downloadable::class,
        'sort' => 5,
    ],

    'virtual' => [
        'key' => 'virtual',
        'name' => 'product::app.type.virtual',
        'class' => Virtual::class,
        'sort' => 6,
    ],

    'booking' => [
        'key' => 'booking',
        'name' => 'product::app.type.booking',
        'class' => Booking::class,
        'sort' => 7,
    ],
];
