<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Toko tidak ditemukan',
            'message' => 'Tidak ada toko di alamat ini. Periksa alamatnya lalu coba lagi.',
        ],

        'suspended' => [
            'title' => 'Toko tidak tersedia',
            'message' => 'Toko ini sedang tidak tersedia. Silakan kembali lagi nanti.',
        ],

        'provisioning' => [
            'title' => 'Toko sedang disiapkan',
            'message' => 'Toko ini sedang disiapkan dan akan siap dalam beberapa saat. Silakan muat ulang halaman sebentar lagi.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute harus terdiri dari 3 sampai 63 huruf kecil, angka, atau tanda hubung, dan tidak boleh diawali atau diakhiri dengan tanda hubung.',
            'reserved' => ':attribute sudah dicadangkan dan tidak dapat digunakan untuk toko.',
            'taken' => ':attribute sudah digunakan oleh toko lain.',
        ],
    ],
];
