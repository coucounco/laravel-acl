<?php
namespace rohsyl\LaravelAcl\Traits;


use Illuminate\Support\Collection;

trait UserAcl
{
    use Acl;

    protected $acl_model = 'user';

    /**
     * Return true if the user has permission with the given parameters
     * @param $permission string The permission
     * @param $arguments array Argument
     * @return bool True if the access is granted
     */
    public function hasAcl(string $permission, array $arguments) {

        $level = $arguments[ACL_ARG_LEVEL] ?? ACL_DELETE;

        $config = config('acl');
        $permissionId = $config['permissions'][$permission];

        $userAcl = null;
        $groupAcl = null;

        // if the user acl is enabled
        if($config['model']['user']['enableAcl']) {
            $userAcl = $this->{$config['model']['user']['attributeName']};
        }

        // if the group acl is enabled
        if($config['model']['group']['enableAcl']) {
            // if one or many groups is given in parameter, then check the permissions only for the given groups
            if(isset($arguments[ACL_ARG_GROUP])) {
                // if a collection of groups is given, then we merge the acl with a permissiv strategy
                // we also filter the collection to remove groups that not belongs to the user
                if($arguments[ACL_ARG_GROUP] instanceof Collection) {
                    $groupAcl = $this->aclMergeCollection($this->aclFilter($arguments[ACL_ARG_GROUP]));
                }
                // we check the the group belongs to the user and then get the acl
                else {
                    $group = $this->aclFilter($arguments[ACL_ARG_GROUP]);
                    if(isset($group)) {
                        $groupAcl = $group->{$config['model']['group']['attributeName']};
                    }
                }
            }
            // if no groups given, then get all groups and merge the acl
            else {
                $groupAcl = $this->aclMergeCollection($this->{$config['model']['group']['relationship']});
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

        // if the user is admin, grant the access
        // else check if the user has the permission
        $granted = $this->aclIsAdmin($acl) || $this->aclIsPermissionGranted($permissionId, $acl, $level);

/*
        ddd([
            'min_level' => $level,
            'permission' => [
                'name' => $permission,
                'id' => $permissionId
            ],
            'acl' => $acl,
            'granted' => $granted ? 'true' : 'false',
            'permissions' => $config['permissions']
        ]);
*/

        return $granted;
    }

    /**
     * Merge a collection of group acl with a permissiv strategy
     * @param Collection $groups
     * @return string The merged acl
     */
    private function aclMergeCollection(Collection $groups = null) {
        if(!isset($groups)) return null;
        $groups = $groups->pluck(config('acl')['model']['group']['attributeName'])->toArray();
        return $this->aclMerge($groups);
    }

    /**
     * Merge a array of acl using a permissiv strategy
     * @param array $groups
     * @return string The merged acl
     */
    private function aclMerge(array $groups) {
        $count = max(array_values(config('acl')['permissions'])) + 1;
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
        return $acl[-1*(config('acl')['permissions']['superadmin']+1)] == ACL_ALLOW;
    }
}