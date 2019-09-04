<?php


namespace rohsyl\LaravelAcl\Traits;


trait Acl
{

    public function setPermission(string $permission, $level) {
        $this->setPermissions([
            $permission => $level
        ]);
    }

    public function setPermissions(array $permissions) {
        if(config('acl')['model'][$this->acl_model]['enableAcl']) {
            $acl = $this->getAcl();
            foreach($permissions as $permission => $level) {
                $this->updateAclPermission($acl, $permission, $level);
            }
            $this->setAcl($acl);
        }
    }

    private function getAcl() {
        return $this->{config('acl')['model'][$this->acl_model]['attributeName']};
    }

    private function setAcl($acl) {
        $this->{config('acl')['model'][$this->acl_model]['attributeName']} = $acl;
    }

    private function updateAclPermission(&$acl, $permission, $level) {
        $permissionId = config('acl')['permissions'][$permission];
        $acl[-1*($permissionId+1)] = $level;
    }
}