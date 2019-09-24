<?php
namespace rohsyl\LaravelAcl\Test;


use Illuminate\Foundation\Testing\DatabaseMigrations;

class HasAclTest extends TestCase
{
    use DatabaseMigrations;

    public function test_has_acl() {

        $this->testUser->grantPermission('user', ACL_UPDATE);

        $this->assertTrue($this->testUser->hasAcl('user', [ACL_READ]));
        $this->assertTrue($this->testUser->hasAcl('user', [ACL_CREATE]));
        $this->assertTrue($this->testUser->hasAcl('user', [ACL_UPDATE]));
        $this->assertFalse($this->testUser->hasAcl('user', [ACL_DELETE]));
    }

    public function test_has_acl_strict() {
        $this->testUser->grantPermission('superadmin', ACL_ALLOW);
        $this->assertFalse($this->testUser->hasAcl('user', [ACL_STRICT]));
        $this->assertTrue($this->testUser->hasAcl('user', ACL_READ));
    }
}