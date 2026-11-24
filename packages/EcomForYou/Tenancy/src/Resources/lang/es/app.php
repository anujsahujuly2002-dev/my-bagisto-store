<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Tienda no encontrada',
            'message' => 'No hay ninguna tienda en esta dirección. Comprueba la dirección e inténtalo de nuevo.',
        ],

        'suspended' => [
            'title' => 'Tienda no disponible',
            'message' => 'Esta tienda no está disponible en este momento. Vuelve a intentarlo más tarde.',
        ],

        'provisioning' => [
            'title' => 'Configurando la tienda',
            'message' => 'Esta tienda se está configurando y estará lista en unos instantes. Actualiza la página en breve.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'El campo :attribute debe tener de 3 a 63 letras minúsculas, números o guiones, y no puede empezar ni terminar con un guion.',
            'reserved' => 'El campo :attribute está reservado y no se puede usar para una tienda.',
            'taken' => 'El campo :attribute ya lo usa otra tienda.',
        ],
    ],
];
