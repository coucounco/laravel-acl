<?php
namespace rohsyl\LaravelAcl\Test;


use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupAclTest extends TestCase
{
    use DatabaseMigrations;

    public function test_group_acl() {
        $group = $this->testGroups->first();

        $this->testUser->groups()->attach($group->id);

        $group->grantPermission('user', ACL_CREATE);
        $group->save();


        $this->assertTrue($this->testUser->can('user', [ACL_READ]));
        $this->assertTrue($this->testUser->can('user', [ACL_CREATE]));
        $this->assertFalse($this->testUser->can('user', [ACL_UPDATE]));

    }

    public function test_specific_group_acl() {

        $group1 = $this->testGroups->first();
        $group2 = $this->testGroups->get(1);

        $this->testUser->groups()->attach([$group1->id, $group2->id]);

        $group1->grantPermission('user', ACL_CREATE);
        $group1->save();
        $group2->grantPermission('page', ACL_CREATE);
        $group2->grantPermission('user', ACL_DELETE);
        $group2->save();

        $this->assertTrue($this->testUser->can('user', [ACL_CREATE, $group1]));
        $this->assertFalse($this->testUser->can('user', [ACL_DELETE, $group1]));
        $this->assertFalse($this->testUser->can('page', [ACL_CREATE, $group1]));

        $this->assertTrue($this->testUser->can('page', [ACL_CREATE, $group2]));
        $this->assertTrue($this->testUser->can('user', [ACL_DELETE, $group2]));
    }

    public function test_revoke_all_acl() {

        $group1 = $this->testGroups->first();
        $group2 = $this->testGroups->get(1);

        $this->testUser->groups()->attach([$group1->id, $group2->id]);

        $group1->grantPermission('user', ACL_CREATE);
        $group1->save();
        $group2->grantPermission('user', ACL_CREATE);
        $group2->grantPermission('page', ACL_CREATE);
        $group2->save();


        $this->assertTrue($this->testUser->can('user', [ACL_CREATE, $group1]));

        $group1->revokePermissions();
        $group1->save();

        $this->assertFalse($this->testUser->can('user', [ACL_CREATE, $group1]));
    }

    public function test_revoke_one_acl() {

        $group1 = $this->testGroups->first();
        $group2 = $this->testGroups->get(1);

        $this->testUser->groups()->attach([$group1->id, $group2->id]);

        $group1->grantPermission('user', ACL_CREATE);
        $group1->save();
        $group2->grantPermission('user', ACL_UPDATE);
        $group2->save();

        $this->assertTrue($this->testUser->can('user', [ACL_CREATE]));
        $this->assertTrue($this->testUser->can('user', [ACL_CREATE, $group1]));

        $group1->revokePermission('user');
        $group1->save();

        $this->assertTrue($this->testUser->can('user', [ACL_CREATE]));
        $this->assertFalse($this->testUser->can('user', [ACL_CREATE, $group1]));
        $this->assertTrue($this->testUser->can('user', [ACL_CREATE, $group2]));

    }
}