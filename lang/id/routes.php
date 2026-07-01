<?php

declare(strict_types=1);

return [
    'home' => '',
    'products.index' => 'produk',
    'products.show' => 'produk/{product:slug}',
    'checkout.create' => 'checkout/{product:slug}',
    'payment.success' => 'pembayaran/sukses',
    'payment.pending' => 'pembayaran/menunggu',
    'payment.error' => 'pembayaran/gagal',
    'payment.finish' => 'pembayaran/selesai',
    'payment.unfinish' => 'pembayaran/batal',
    'pages.show' => 'halaman/{page:slug}',
    'blog.index' => 'artikel',
    'blog.show' => 'artikel/{post:slug}',
    'customer.dashboard' => 'pelanggan/dashboard',
    'customer.downloads' => 'pelanggan/unduhan',
    'customer.order.show' => 'pelanggan/pesanan/{order:order_number}',
    'customer.profile' => 'pelanggan/profil',
    'payment.download' => 'unduh/{order:order_number}/{product}',
];
