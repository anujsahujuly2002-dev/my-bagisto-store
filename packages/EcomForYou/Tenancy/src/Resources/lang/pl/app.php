<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Nie znaleziono sklepu',
            'message' => 'Pod tym adresem nie ma żadnego sklepu. Sprawdź adres i spróbuj ponownie.',
        ],

        'suspended' => [
            'title' => 'Sklep niedostępny',
            'message' => 'Ten sklep jest w tej chwili niedostępny. Zajrzyj ponownie później.',
        ],

        'provisioning' => [
            'title' => 'Sklep jest konfigurowany',
            'message' => 'Ten sklep jest właśnie konfigurowany i za chwilę będzie gotowy. Odśwież stronę za moment.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => 'Pole :attribute musi zawierać od 3 do 63 małych liter, cyfr lub myślników i nie może zaczynać się ani kończyć myślnikiem.',
            'reserved' => 'Pole :attribute jest zarezerwowane i nie może być użyte dla sklepu.',
            'taken' => 'Pole :attribute jest już zajęte przez inny sklep.',
        ],
    ],
];
