<?php


namespace rohsyl\LaravelAcl\Traits;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait Acl
{
    protected $acls = null;

    /**
     * Clear all cache entries for this user
     */
    public function aclClearCache() {
        //$driver = config('cache.default');
        // Cache tags are not supported when using the file or  database cache drivers.
        //if($driver == 'file' || $driver == 'database') {
            Cache::flush();
        //}
        //else {
        //    Cache::tags('laravel-acl-user-'.$this->id)->flush();
        //}
    }

    /**
     * Grant the given permission with the given level
     * @param string $permission The permission
     * @param $level mixed The level
     */
    public function grantPermission(string $permission, $level) {
        $this->grantPermissions([
            $permission => $level
        ]);
    }

    /**
     * Grant many permissions
     * $permissions is a key/value array where key is the permission and value is the level
     * @param array $permissions
     */
    public function grantPermissions(array $permissions) {
        $this->aclClearCache();
        if($this->getConfig()['model'][$this->acl_model]['enableAcl']) {
            $acl = $this->getAcl();
            foreach($permissions as $permission => $level) {
                $this->updateAclPermission($acl, $permission, $level);
            }
            $this->setAcl($acl);
        }
    }

    /**
     * Revoke a permission
     * @param string $permission The permission
     */
    public function revokePermission(string $permission) {
        $this->grantPermissions([
            $permission => ACL_NONE
        ]);
    }

    /**
     * Revoke many or all permission
     * If $permissions is not set, then all permissions will be revoked
     * @param array|null $permissions
     */
    public function revokePermissions(array $permissions = null) {
        $permissions = $permissions ?? array_keys(config('acl')['permissions']);
        $this->grantPermissions(array_fill_keys($permissions, ACL_NONE));
    }

    /**
     * Get the acl
     * @return mixed
     */
    private function getAcl() {
        $attributeName = $this->getConfig()['model'][$this->acl_model]['attributeName'];
        $acl = $this->$attributeName;
        return isset($acl) && !empty($acl) ? $acl : $this->getDefaultAcl();
    }

    private function getDefaultAcl() {
        return acl_empty();
    }

    /**
     * Set the acl
     * @param $acl string
     */
    private function setAcl($acl) {
        $this->{$this->getConfig()['model'][$this->acl_model]['attributeName']} = $acl;
    }

    /**
     * Update the $permission with the given $level on the given $acl
     * @param $acl string
     * @param $permission string
     * @param $level mixed
     */
    private function updateAclPermission(&$acl, $permission, $level) {
        $permissionId = $this->getPermissions()[$permission];
        if(strlen($acl) < $permissionId) {
            $acl = str_pad($acl, $permissionId+1, ACL_NONE, STR_PAD_LEFT);
        }
        $acl[-1*($permissionId+1)] = $level;
    }

    private function getCacheKey($permissionId = null, $level = null, $teams = null, $strict = false) {
        $cacheKey = config('acl.cache.key') ?? 'laravel-acl_';
        $key = $cacheKey . $this->id;

        if(isset($permissionId)) {
            $key .= ':' . $permissionId;

            if(isset($level)) {
                $key .= ':' . $level;
            }

            if (isset($teams)) {
                if(config('acl.model.group.enableAcl')) {
                    if($teams instanceof Collection) {
                        $key .= '_g:' . $teams->pluck('id')->join(',');
                    }
                    else {
                        $key .= '_g:' . $teams->id;
                    }
                }
            }
        }
        if($strict) {
            $key .= '_strict';
        }

        return $key;
    }

    public function aclModelType() {
        return $this->acl_model;
    }

    private function getAcls() {
        return $this->acls ?? config('acl.models')[get_class($this)] ?? config('acl.defaults.acls', 'users');
    }

    private function getPermissions() {
        return acl_permissions($this->getAcls());
    }

    private function getConfig($config = null) {
        return config('acl.'.$this->getAcls() . (isset($config) ? '.' . $config : ''));
    }
}