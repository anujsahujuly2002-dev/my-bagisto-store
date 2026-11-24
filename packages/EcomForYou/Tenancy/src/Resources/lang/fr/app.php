<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Boutique introuvable',
            'message' => 'Il n\'y a aucune boutique à cette adresse. Vérifiez l\'adresse et réessayez.',
        ],

        'suspended' => [
            'title' => 'Boutique indisponible',
            'message' => 'Cette boutique n\'est pas disponible pour le moment. Veuillez revenir plus tard.',
        ],

        'provisioning' => [
            'title' => 'Boutique en cours de configuration',
            'message' => 'Cette boutique est en cours de configuration et sera prête dans quelques instants. Veuillez actualiser la page sous peu.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'Le champ :attribute doit comporter de 3 à 63 lettres minuscules, chiffres ou tirets, et ne peut pas commencer ni se terminer par un tiret.',
            'reserved' => 'Le champ :attribute est réservé et ne peut pas être utilisé pour une boutique.',
            'taken' => 'Le champ :attribute est déjà utilisé par une autre boutique.',
        ],
    ],
];
