<?php
return [
    /**
     * Register every permissions here
     * key is the name of the permission
     * value is the id of the permission
     * Every permissions can have a different access level
     *    0 = ACL_NONE    -> no permission
     *    1 = ACL_READ    -> read permission
     *    2 = ACL_CREATE  -> create permission
     *    3 = ACL_UPDATE  -> update permission
     *    4 = ACL_DELETE  -> delete permission
     * Access level are incrmental. If user has a permission with an access level of ACL_CREATE
     * it means that the user can ACL_READ and ACL_CREATE
     */
    'permissions' => [
        'superadmin' => 0,
        'user' => 1,
        'group' => 2,
        'page' => 3,
    ],

    /**
     *
     */
    'roles' => [
        'admins' =>     "1",
        'user' =>   "4110",
    ],

    'model' => [
        // direct acl on user
        'user' => [
            /**
             * Enable acl for user
             */
            'enableAcl' => true,
            /**
             * The attribute where the acl is stored in the User model.
             * It's basically the name of the column in the database
             */
            'attributeName' => 'acl',
        ],
        // acl via groups of user
        // n-n relation between user and group
        'group' => [
            /**
             * Enable acl for group
             */
            'enableAcl' => false,
            /**
             * The name of the relationship from the User model to the "group" model
             */
            'relationship' => 'groups',
            /**
             * The attribute where the acl is stored in the "group" model.
             * It's basically the name of the column in the database
             */
            'attributeName' => 'acl',
        ]
    ]
];
