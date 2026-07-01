<?php

declare(strict_types=1);

return [
    'home' => '',
    'products.index' => 'products',
    'products.show' => 'products/{product:slug}',
    'checkout.create' => 'checkout/{product:slug}',
    'payment.success' => 'payment/success',
    'payment.pending' => 'payment/pending',
    'payment.error' => 'payment/error',
    'payment.finish' => 'payment/finish',
    'payment.unfinish' => 'payment/unfinish',
    'pages.show' => 'pages/{page:slug}',
    'blog.index' => 'blog',
    'blog.show' => 'blog/{post:slug}',
    'customer.dashboard' => 'customer/dashboard',
    'customer.downloads' => 'customer/downloads',
    'customer.order.show' => 'customer/orders/{order:order_number}',
    'customer.profile' => 'customer/profile',
    'payment.download' => 'download/{order:order_number}/{product}',
];
