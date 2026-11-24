<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Loja não encontrada',
            'message' => 'Não há nenhuma loja neste endereço. Verifique o endereço e tente novamente.',
        ],

        'suspended' => [
            'title' => 'Loja indisponível',
            'message' => 'Esta loja não está disponível no momento. Volte mais tarde.',
        ],

        'provisioning' => [
            'title' => 'Loja em configuração',
            'message' => 'Esta loja está sendo configurada e estará pronta em instantes. Atualize a página em breve.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'O campo :attribute deve ter de 3 a 63 letras minúsculas, números ou hifens, e não pode começar nem terminar com hífen.',
            'reserved' => 'O campo :attribute é reservado e não pode ser usado para uma loja.',
            'taken' => 'O campo :attribute já está sendo usado por outra loja.',
        ],
    ],
];
