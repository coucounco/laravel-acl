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

if(!function_exists('acl_can')) {

    function acl_can($model) {
        $user = auth()->user();
    }
}


function ddd($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}