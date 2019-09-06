<?php


namespace rohsyl\LaravelAcl\Traits;


trait Acl
{

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
        if(config('acl')['model'][$this->acl_model]['enableAcl']) {
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
        return $this->{config('acl')['model'][$this->acl_model]['attributeName']} ?? $this->getDefaultAcl();
    }

    private function getDefaultAcl() {
        return acl_empty();
    }

    /**
     * Set the acl
     * @param $acl string
     */
    private function setAcl($acl) {
        $this->{config('acl')['model'][$this->acl_model]['attributeName']} = $acl;
    }

    /**
     * Update the $permission with the given $level on the given $acl
     * @param $acl string
     * @param $permission string
     * @param $level mixed
     */
    private function updateAclPermission(&$acl, $permission, $level) {
        $permissionId = config('acl')['permissions'][$permission];
        if(strlen($acl) < $permissionId) {
            $acl = str_pad($acl, $permissionId+1, ACL_NONE, STR_PAD_LEFT);
        }
        $acl[-1*($permissionId+1)] = $level;
    }
}