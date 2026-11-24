<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Store not found',
            'message' => 'There is no store at this address. Check the address and try again.',
        ],

        'suspended' => [
            'title' => 'Store unavailable',
            'message' => 'This store is not available at the moment. Please check back later.',
        ],

        'provisioning' => [
            'title' => 'Store being set up',
            'message' => 'This store is being set up and will be ready in a few moments. Please refresh the page shortly.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'The :attribute must be 3 to 63 lowercase letters, numbers or dashes, and cannot start or end with a dash.',
            'reserved' => 'The :attribute is reserved and cannot be used for a store.',
            'taken' => 'The :attribute is already taken by another store.',
        ],
    ],
];
