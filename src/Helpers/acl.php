<?php

define('ACL_NONE',      0);
define('ACL_READ',      1);
define('ACL_CREATE',    2);
define('ACL_UPDATE',    3);
define('ACL_DELETE',    4);

define('ACL_DENY',      0);
define('ACL_ALLOW',      1);

define('ACL_ARG_LEVEL', 0);
define('ACL_ARG_GROUP', 1);

if(!function_exists('acl_permissions')) {
    /**
     * Get all permissions
     * @return array
     */
    function acl_permissions() {
        return config('acl.permissions');
    }
}

if(!function_exists('acl_roles')) {
    /**
     * Get all roles
     * @return array
     */
    function acl_roles() {
        return config('acl.roles');
    }
}

if(!function_exists('acl_has_permission')) {
    /**
     * Check if the $user has the $permission with the access $level
     * @param $user
     * @param $permission
     * @param $level
     * @return boolean
     */
    function acl_has_permission($user, $permission, $level) {
        return $user->hasAcl($permission, [$level]);
    }
}

if(!function_exists('acl_empty')) {
    /**
     * Get an empy ACL string
     * @return string
     */
    function acl_empty() {
        $count = max(array_values(config('acl')['permissions'])) + 1;
        return str_repeat(ACL_NONE, $count );
    }
}


if(!function_exists('acl_permission_level')) {
    function acl_permission_level($user, $permission)
    {
        $permissionId = config('acl')['permissions'][$permission];
        $acl = isset($user->acl) && !empty($user->acl) ? $user->acl : acl_empty();
        return $acl[-1 * ($permissionId + 1)];
    }
}

function ddd($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}