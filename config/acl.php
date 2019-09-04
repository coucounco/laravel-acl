<?php
return [
    /**
     * Register every roles here
     * key is the name of the role
     * value is the id of the role
     * Every permissions can have a different access level
     *    0 = ACL_NONE    -> no permission
     *    1 = ACL_READ    -> read permission
     *    2 = ACL_CREATE  -> create permission
     *    3 = ACL_UPDATE  -> update permission
     *    4 = ACL_DELETE  -> delete permission
     * Access level are incrental. If user has a permission with an access level of ACL_CREATE
     * it means that the user can ACL_READ and ACL_CREATE
     */
    'permissions' => [
        'superadmin' => 0,

        'user' => 1,
        'team' => 2,
        'rental' => 3,
        'owner' => 4,
    ],

    /**
     *
     */
    'roles' => [
        'admins' =>     "00001",
        'cleaning' =>   "01000",
    ],



    'model' => [
        // direct acl on user
        'user' => [
            'enableAcl' => false,
            'attributeName' => 'acl',
        ],
        // acl via groups of user
        // n-n relation between user and group
        'group' => [
            'enableAcl' => true,
            'class' => 'App\Models\Team',
            'attributeName' => 'acl',
        ]
    ]


];
