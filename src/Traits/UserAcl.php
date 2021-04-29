<?php
namespace rohsyl\LaravelAcl\Traits;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait UserAcl
{
    use Acl;

    protected $acl_model = 'user';

    /**
     * Return true if the user has permission with the given parameters
     * @param $permission string The permission
     * @param $arguments array|mixed Argument
     * @return bool True if the access is granted
     */
    public function hasAcl(string $permission, $arguments) {
        // ignore if the permission doesn't exists in the configuration
        $config = $this->getPermissions();
        if(!isset($config[$permission])) return null;

        $level = is_array($arguments)
            ? $arguments[ACL_ARG_LEVEL] ?? ACL_NONE
            : $arguments;

        // strict is used to check if the user strictly have the given permission.
        // if the user don't have it, false will be returned even if the user is superadmin
        $strict = false;
        if($level === ACL_STRICT) {
            $strict = true;
            $level = ACL_ALLOW;
        }

        $permissionId = $config[$permission];
        $teams = is_array($arguments) && isset($arguments[ACL_ARG_GROUP]) ? $arguments[ACL_ARG_GROUP] : null;

        $closure = function() use ($arguments, $permissionId, $level, $strict) {

            $userAcl = null;
            $groupAcl = null;


            // if the acl is disabled for both user and group
            if(!$this->getConfig('model.user.enableAcl') && !$this->getConfig('model.group.enableAcl')) return null;

            // if the user acl is enabled
            if($this->getConfig('model.user.enableAcl')) {
                $userAcl = $this->getAcl();
            }

            // if the group acl is enabled
            if($this->getConfig('model.group.enableAcl')) {

                // if one or many groups is given in parameter, then check the permissions only for the given groups
                if(is_array($arguments) && isset($arguments[ACL_ARG_GROUP])) {
                    // if a collection of groups is given, then we merge the acl with a permissiv strategy
                    // we also filter the collection to remove groups that not belongs to the user

                    if($arguments[ACL_ARG_GROUP] instanceof Collection) {
                        $groupAcl = $this->aclMergeCollection($this->aclFilter($arguments[ACL_ARG_GROUP]));
                    }
                    // we check the the group belongs to the user and then get the acl
                    else {
                        $group = $this->aclFilter($arguments[ACL_ARG_GROUP]);
                        if(isset($group)) {
                            $groupAcl = $group->{$this->getConfig('model.group.attributeName')};
                            $groupAcl = isset($groupAcl) && !empty($groupAcl) ? $groupAcl : $this->getDefaultAcl();
                        }
                    }
                }
                // if no groups given, then get all groups and merge the acl
                else {
                    $groupAcl = $this->aclMergeCollection($this->{$this->getConfig('model.group.relationship')});
                }

            }

            // if user and group has ACL, merge it
            if(isset($userAcl) && isset($groupAcl)) {
                $acl = $this->aclMerge([$userAcl, $groupAcl]);
            }
            // else return the user acl or the group or null
            else {
                $acl = $userAcl ?? $groupAcl ?? null;
            }

            // the strict option check if the user has the given permission only
            // so if the user didn't have this permission, false will be returned, even if the user is admin
            if($strict) {
                return $this->aclIsPermissionGranted($permissionId, $acl, $level);
            }
            else {
                // if the user is admin, grant the access
                // else check if the user has the permission
                return $this->aclIsAdmin($acl) || $this->aclIsPermissionGranted($permissionId, $acl, $level);
            }
        };

        $key = $this->getCacheKey($permissionId, $level, $teams, $strict);
        $cacheExpirationTime = config('acl.cache.expiration_time') ?? 60 * 60 * 24 * 5;

        // Cache tags are not supported when using the file or  database cache drivers.
        //$driver = config('cache.default');
        //if($driver == 'file' || $driver == 'database') {
        if(config('acl.cache.enable')) {
            return Cache::remember($key, $cacheExpirationTime, $closure);
        }
        else {
            return $closure();
        }
        //}
        //else {
        //    return Cache::tags(['laravel-acl', 'laravel-acl-user-'.$this->id])->remember($key, $cacheExpirationTime, $closure);
        //}
    }


    /**
     * Check if the user has any of the given permissions
     * @param array $permissions
     * @return bool
     */
    public function hasAclAny(array $permissions) {
        foreach($permissions as $permission) {
            $permissionName = array_key_first($permission);
            $level = $permission[$permissionName];
            if($this->hasAcl($permissionName, [$level])) return true;
        }
        return false;
    }

    /**
     * Merge a collection of group acl with a permissiv strategy
     * @param Collection $groups
     * @return string The merged acl
     */
    private function aclMergeCollection(Collection $groups = null) {
        if(!isset($groups)) return null;
        $groups = $groups->pluck($this->getConfig('model.group.attributeName'))->toArray();
        return $this->aclMerge($groups);
    }

    /**
     * Merge a array of acl using a permissiv strategy
     * @param array $groups
     * @return string The merged acl
     */
    private function aclMerge(array $groups) {
        $count = max(array_values($this->getPermissions())) + 1;
        $out = str_repeat(ACL_NONE, $count );
        for($i = 0; $i < $count; $i++) {
            $permMerged = ACL_NONE;
            foreach($groups as $group) {
                $permissionLevel = $group[-1*($i+1)] ?? ACL_NONE;
                if(is_numeric($permissionLevel)) {
                    $numericPermissionLevel = intval($permissionLevel);
                    $permMerged = max($permMerged, $numericPermissionLevel);
                }
            }
            $out[-1*($i+1)] = $permMerged;
        }
        return $out;
    }

    /**
     * Check if the permission is granted
     * @param $permissionId integer The id of the permission
     * @param $acl string The acl
     * @param $minLevel integer|string The min accepted level
     * @return bool
     */
    private function aclIsPermissionGranted($permissionId, $acl, $minLevel) {
        if(!isset($acl)) return false;
        $userLevel = $acl[-1*($permissionId+1)] ?? ACL_NONE;
        if(is_numeric($userLevel)) {
            $numericPermissionLevel = intval($userLevel);
            return $numericPermissionLevel !== ACL_NONE && $numericPermissionLevel >= $minLevel;
        }
        else {
            return $userLevel === $minLevel;
        }
    }

    /**
     * Filter a group or a list of group to remove group that dont belongs to the user
     * @param $groups
     * @return Collection|object
     */
    public function aclFilter($groups) {
        if($groups instanceof Collection) {
            $filtered = $groups->filter(function ($group) {
                return $group->users->contains($this);
            });
            return $filtered;
        }
        else {
            if($groups->users->contains($this))
                return $groups;
            else
                return null;
        }

    }

    /**
     * Does the given acl grant an admin access
     * @param $acl string The acl
     * @return bool
     */
    private function aclIsAdmin($acl) {
        if(!isset($acl)) return false;
        return $acl[-1*($this->getConfig('permissions.superadmin')+1)] == ACL_ALLOW;
    }
}