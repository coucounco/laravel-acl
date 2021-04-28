<?php
return [

    'defaults' => [
        'acls' => 'users',
    ],

    'models' => [
        \rohsyl\OmegaCore\Models\User::class => 'users',
    ],

    'cache' => [
        'enable' => true,
        'key' => 'laravel-acl_',
        'store' => '',
        'expiration_time' => 432000
    ]
];
