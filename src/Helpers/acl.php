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
    function acl_permissions() {
        return config('acl.permissions');
    }
}

if(!function_exists('acl_roles')) {
    function acl_roles() {
        return config('acl.roles');
    }
}

if(!function_exists('acl_has_permission')) {
    function acl_has_permission($user, $permission, $level) {
        return $user->hasAcl($permission, [$level]);
    }
}

function ddd($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}