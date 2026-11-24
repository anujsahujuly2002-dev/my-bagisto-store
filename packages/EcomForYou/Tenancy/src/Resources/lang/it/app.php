<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Negozio non trovato',
            'message' => 'Non esiste alcun negozio a questo indirizzo. Controlla l\'indirizzo e riprova.',
        ],

        'suspended' => [
            'title' => 'Negozio non disponibile',
            'message' => 'Questo negozio non è al momento disponibile. Riprova più tardi.',
        ],

        'provisioning' => [
            'title' => 'Negozio in fase di configurazione',
            'message' => 'Questo negozio è in fase di configurazione e sarà pronto tra pochi istanti. Aggiorna la pagina a breve.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'Il campo :attribute deve contenere da 3 a 63 lettere minuscole, numeri o trattini, e non può iniziare o terminare con un trattino.',
            'reserved' => 'Il campo :attribute è riservato e non può essere usato per un negozio.',
            'taken' => 'Il campo :attribute è già usato da un altro negozio.',
        ],
    ],
];
