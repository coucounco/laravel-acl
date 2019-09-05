<?php
namespace rohsyl\LaravelAcl\Test;


use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

class BladeAclTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->be($this->testUser);
    }

    public function test_can_blade_directive() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->testUser->save();

        $this->assertEquals('allow', $this->renderView('can'));

        $this->testUser->revokePermission('user');
        $this->testUser->save();

        $this->assertEquals('denied', $this->renderView('can'));
    }

    public function test_cannot_blade_directive() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->testUser->save();

        $this->assertEquals('allow', $this->renderView('cannot'));

        $this->testUser->revokePermission('user');
        $this->testUser->save();

        $this->assertEquals('denied', $this->renderView('cannot'));
    }

    public function test_canany_blade_directive() {

        $this->testUser->grantPermission('user', ACL_READ);
        $this->testUser->grantPermission('group', ACL_READ);
        $this->testUser->save();

        $this->assertEquals('user or page', $this->renderView('canany'));

        $this->testUser->revokePermission('user');
        $this->testUser->save();

        $this->assertEquals('allow', $this->renderView('canany'));

        $this->testUser->revokePermission('group');
        $this->testUser->save();

        $this->assertEquals('denied', $this->renderView('canany'));
    }

    protected function renderView($view, $parameters = [])
    {
        Artisan::call('view:clear');
        if (is_string($view)) {
            $view = view($view)->with($parameters);
        }
        return trim((string) ($view));
    }
}