<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'No s\'ha trobat la botiga',
            'message' => 'No hi ha cap botiga en aquesta adreça. Comprova l\'adreça i torna-ho a provar.',
        ],

        'suspended' => [
            'title' => 'Botiga no disponible',
            'message' => 'Aquesta botiga no està disponible en aquest moment. Torna-ho a provar més tard.',
        ],

        'provisioning' => [
            'title' => 'S\'està configurant la botiga',
            'message' => 'S\'està configurant aquesta botiga i estarà llesta d\'aquí a uns moments. Actualitza la pàgina d\'aquí a poc.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'El camp :attribute ha de tenir de 3 a 63 lletres minúscules, números o guions, i no pot començar ni acabar amb un guió.',
            'reserved' => 'El camp :attribute està reservat i no es pot fer servir per a una botiga.',
            'taken' => 'El camp :attribute ja l\'utilitza una altra botiga.',
        ],
    ],
];
