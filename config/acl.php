<?php
return [

    /*
     * Defaults for laravel-acl
     */
    'defaults' => [
        /**
         * The acls consists in a set of permissions for a given entity.
         * Here you can set the default acls (acls defined in the models section of this config file) used by laravel-acl
         */
        'acls' => 'users',
    ],

    /**
     * Here you map models to acls.
     *
     * You can add new row by adding :
     *      [Model] => [acls_name]
     *
     * After adding a new line here, you will need to add a new configuration files under config/acl/[acls_name].php
     */
    'models' => [
        App\Models\User::class => 'users',
    ],

    /**
     * Cache configuration
     */
    'cache' => [
        'enable' => true,
        'key' => 'laravel-acl_',
        'store' => '',
        'expiration_time' => 432000
    ],
];
