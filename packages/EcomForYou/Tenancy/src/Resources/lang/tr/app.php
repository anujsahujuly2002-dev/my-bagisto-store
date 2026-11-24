<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Mağaza bulunamadı',
            'message' => 'Bu adreste bir mağaza yok. Adresi kontrol edip tekrar deneyin.',
        ],

        'suspended' => [
            'title' => 'Mağaza kullanılamıyor',
            'message' => 'Bu mağaza şu anda kullanılamıyor. Lütfen daha sonra tekrar bakın.',
        ],

        'provisioning' => [
            'title' => 'Mağaza kuruluyor',
            'message' => 'Bu mağaza kuruluyor ve birkaç dakika içinde hazır olacak. Lütfen sayfayı birazdan yenileyin.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute 3 ila 63 küçük harf, rakam veya tireden oluşmalı ve tireyle başlayamaz veya bitemez.',
            'reserved' => ':attribute ayrılmıştır ve bir mağaza için kullanılamaz.',
            'taken' => ':attribute zaten başka bir mağaza tarafından kullanılıyor.',
        ],
    ],
];
