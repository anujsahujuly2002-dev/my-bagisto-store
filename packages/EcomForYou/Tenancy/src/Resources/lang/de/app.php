<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Shop nicht gefunden',
            'message' => 'Unter dieser Adresse gibt es keinen Shop. Prüfen Sie die Adresse und versuchen Sie es erneut.',
        ],

        'suspended' => [
            'title' => 'Shop nicht verfügbar',
            'message' => 'Dieser Shop ist derzeit nicht verfügbar. Bitte schauen Sie später wieder vorbei.',
        ],

        'provisioning' => [
            'title' => 'Shop wird eingerichtet',
            'message' => 'Dieser Shop wird gerade eingerichtet und ist in wenigen Augenblicken bereit. Bitte laden Sie die Seite gleich neu.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute muss aus 3 bis 63 Kleinbuchstaben, Ziffern oder Bindestrichen bestehen und darf nicht mit einem Bindestrich beginnen oder enden.',
            'reserved' => ':attribute ist reserviert und kann nicht für einen Shop verwendet werden.',
            'taken' => ':attribute wird bereits von einem anderen Shop verwendet.',
        ],
    ],
];
