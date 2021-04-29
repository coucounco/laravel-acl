<?php
return [

    'defaults' => [
        'acls' => 'users',
    ],

    'models' => [
        App\Models\User::class => 'users',
    ],

    'cache' => [
        'enable' => true,
        'key' => 'laravel-acl_',
        'store' => '',
        'expiration_time' => 432000
    ],
];
