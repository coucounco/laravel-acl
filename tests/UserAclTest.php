<?php
namespace rohsyl\LaravelAcl\Test;


use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserAclTest extends TestCase
{
    use DatabaseMigrations;

    public function test_permission_doesnt_exists() {

        $this->assertFalse($this->testUser->can('user', [ACL_READ]));
        $this->assertNull($this->testUser->hasAcl('permissions_that_doesnt_exists', [ACL_READ]));
        //$this->assertNull($this->testUser->can('permissions_that_doesnt_exists', [ACL_READ]));
    }

    public function test_simple_acl() {

        $this->testUser->grantPermission('user', ACL_READ);

        $this->assertTrue($this->testUser->can('user', [ACL_READ]));
        $this->assertFalse($this->testUser->can('user', [ACL_CREATE]));
    }

    public function test_revoke_acl() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->assertTrue($this->testUser->can('user', [ACL_READ]));

        $this->testUser->revokePermission('user');
        $this->assertFalse($this->testUser->can('user', [ACL_READ]));

    }

    public function test_revoke_all_acl() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->testUser->grantPermission('group', ACL_CREATE);
        $this->testUser->grantPermission('page', ACL_DELETE);

        $this->assertTrue($this->testUser->can('user', [ACL_READ]));
        $this->assertTrue($this->testUser->can('group', [ACL_READ]));
        $this->assertTrue($this->testUser->can('group', [ACL_CREATE]));
        $this->assertTrue($this->testUser->can('page', [ACL_READ]));
        $this->assertTrue($this->testUser->can('page', [ACL_CREATE]));
        $this->assertTrue($this->testUser->can('page', [ACL_UPDATE]));
        $this->assertTrue($this->testUser->can('page', [ACL_DELETE]));

        $this->testUser->revokePermissions();

        $this->assertFalse($this->testUser->can('user', [ACL_READ]));
        $this->assertFalse($this->testUser->can('group', [ACL_CREATE]));
        $this->assertFalse($this->testUser->can('page', [ACL_DELETE]));

    }


    public function test_admin_acl() {

        $this->testUser->grantPermission('superadmin', ACL_ALLOW);

        $this->assertTrue($this->testUser->can('user', [ACL_DELETE]));
    }

    public function test_can_with_no_level() {
        $this->testUser->grantPermission('user', ACL_READ);
        $this->assertTrue($this->testUser->can('user'));
    }

    public function test_can_with_level() {
        $this->testUser->grantPermission('user', ACL_READ);
        $this->assertTrue($this->testUser->can('user', ACL_READ));
    }


    public function test_can_strict() {
        $this->testUser->grantPermission('superadmin', ACL_ALLOW);
        $this->assertFalse($this->testUser->can('user', ACL_STRICT));
        $this->assertTrue($this->testUser->can('user'));
    }
}