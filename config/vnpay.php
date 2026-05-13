<?php

declare(strict_types=1);

return [

    'tmn_code' => env('VNPAY_TMN_CODE', ''),
    'hash_secret' => env('VNPAY_HASH_SECRET', ''),

    'payment_url' => env('VNPAY_PAYMENT_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),

    'expire_minutes' => (int) env('VNPAY_EXPIRE_MINUTES', 15),

    'locale' => env('VNPAY_LOCALE', 'vn'),

    'command' => 'pay',

    'curr_code' => 'VND',

    'version' => env('VNPAY_VERSION', '2.1.0'),

];
