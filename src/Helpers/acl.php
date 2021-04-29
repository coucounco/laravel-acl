<?php

define('ACL_NONE',      0);
define('ACL_READ',      1);
define('ACL_CREATE',    2);
define('ACL_UPDATE',    3);
define('ACL_DELETE',    4);

define('ACL_DENY',      0);
define('ACL_ALLOW',     1);
define('ACL_STRICT',   10);

define('ACL_ARG_LEVEL', 0);
define('ACL_ARG_GROUP', 1);

if(!function_exists('acl_permissions')) {
    /**
     * Get all permissions
     * @return array
     */
    function acl_permissions($acls = null) {
        $acls = $acls ?? config('acl.defaults.acls', 'users');
        return config('acl.'.$acls.'.permissions');
    }
}

if(!function_exists('acl_roles')) {
    /**
     * Get all roles
     * @return array
     */
    function acl_roles($acls = null) {
        $acls = $acls ?? config('acl.defaults.acls', 'users');
        return config('acl.'.$acls.'.roles');
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
    function acl_empty($acls = null) {

        $count = max(array_values(acl_permissions($acls))) + 1;
        return str_repeat(ACL_NONE, $count );
    }
}


if(!function_exists('acl_permission_level')) {
    function acl_permission_level($entity, $permission, $acls = null)
    {
        $acls = $acls ?? config('acl.defaults.acls', 'users');
        $column = config('acl.'.$acls)['model'][$entity->aclModelType()]['attributeName'];
        $permissionId = config('acl.'.$acls)['permissions'][$permission];
        $acl = isset($entity->$column) && !empty($entity->$column) ? $entity->$column : acl_empty();
        return $acl[-1 * ($permissionId + 1)] ?? ACL_NONE;
    }
}

if (! function_exists('aclx_roles_to_select')) {
    /**
     * Format acl roles to be used in a html select tag.
     *
     * @param $roles
     *
     * @return array|null
     */
    function aclx_roles_to_select($roles)
    {
        array_walk($roles, function (&$value, $key) {
            $value = "$key:$value";
        });
        $roles = array_flip($roles);

        return $roles;
    }
}
if (! function_exists('aclx_value')) {
    /**
     * Parse the acl role value from the form.
     *
     * @param $selectValue
     *
     * @return string
     */
    function aclx_value($selectValue)
    {
        return explode(':', $selectValue)[1] ?? '';
    }
}
if (! function_exists('aclx_group')) {
    function aclx_group($permissions)
    {
        $groups = [];

        foreach ($permissions as $perm => $value) {
            $subperms = explode('.', $perm);

            $max = sizeof($subperms);
            $groups = array_merge($groups, aclx_group_recursive($subperms, $groups, 0, $max));
        }

        return $groups;
    }
}
if (! function_exists('aclx_group_recursive')) {
    function aclx_group_recursive($subperms, $group, $i, $max)
    {
        if (isset($subperms[$i])) {
            if (! isset($group[$subperms[$i]])) {
                $group[$subperms[$i]] = [];
            }
            $group[$subperms[$i]] = array_merge($group[$subperms[$i]], aclx_group_recursive($subperms, $group[$subperms[$i]], ++$i, $max));
        }

        return $group;
    }
}