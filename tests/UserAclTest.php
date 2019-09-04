<?php


namespace rohsyl\LaravelAcl\Test;


class UserAclTest extends TestCase
{

    public function test_simple_acl() {

        $this->testUser->grantPermission('user', ACL_READ);

        $this->assertTrue($this->testUser->can('user', [ACL_READ]));
    }

    public function test_revoke_acl() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->assertTrue($this->testUser->can('user', [ACL_READ]));

        $this->testUser->revokePermission('user');
        $this->assertFalse($this->testUser->can('user', [ACL_READ]));

    }
}