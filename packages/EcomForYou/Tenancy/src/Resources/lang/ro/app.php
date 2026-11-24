<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Magazinul nu a fost găsit',
            'message' => 'Nu există niciun magazin la această adresă. Verificați adresa și încercați din nou.',
        ],

        'suspended' => [
            'title' => 'Magazin indisponibil',
            'message' => 'Acest magazin nu este disponibil în acest moment. Reveniți mai târziu.',
        ],

        'provisioning' => [
            'title' => 'Magazinul este în curs de configurare',
            'message' => 'Acest magazin este în curs de configurare și va fi gata în câteva momente. Reîmprospătați pagina în curând.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'Câmpul :attribute trebuie să conțină între 3 și 63 de litere mici, cifre sau cratime și nu poate începe sau se termina cu o cratimă.',
            'reserved' => 'Câmpul :attribute este rezervat și nu poate fi folosit pentru un magazin.',
            'taken' => 'Câmpul :attribute este deja folosit de un alt magazin.',
        ],
    ],
];
