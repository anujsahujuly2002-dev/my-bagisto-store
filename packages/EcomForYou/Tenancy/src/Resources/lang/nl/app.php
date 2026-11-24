<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Winkel niet gevonden',
            'message' => 'Er is geen winkel op dit adres. Controleer het adres en probeer het opnieuw.',
        ],

        'suspended' => [
            'title' => 'Winkel niet beschikbaar',
            'message' => 'Deze winkel is op dit moment niet beschikbaar. Kom later terug.',
        ],

        'provisioning' => [
            'title' => 'Winkel wordt ingericht',
            'message' => 'Deze winkel wordt ingericht en is over enkele ogenblikken klaar. Vernieuw de pagina zo meteen.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute moet bestaan uit 3 tot 63 kleine letters, cijfers of streepjes, en mag niet beginnen of eindigen met een streepje.',
            'reserved' => ':attribute is gereserveerd en kan niet voor een winkel worden gebruikt.',
            'taken' => ':attribute is al in gebruik door een andere winkel.',
        ],
    ],
];
