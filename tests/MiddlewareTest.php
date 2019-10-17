<?php


namespace rohsyl\LaravelAcl\Test;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use rohsyl\LaravelAcl\Exceptions\UnauthorizedException;
use rohsyl\LaravelAcl\Middlewares\AclMiddleware;

class MiddlewareTest extends TestCase
{
    private $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->middleware = new AclMiddleware($this->app);
    }

    public function test_guest_cannot_access() {
        $status = $this->runMiddleware(
            $this->middleware, 'user:1'
        );
        $this->assertEquals($status, 403);
    }

    public function test_user_can_access() {
        $this->be($this->testUser);

        $this->testUser->grantPermission('user',  ACL_READ);

        $status = $this->runMiddleware(
            $this->middleware, 'user:1'
        );

        $this->assertEquals($status, 200);
    }

    public function test_user_can_access_no_parameter() {
        $this->be($this->testUser);

        $this->testUser->grantPermission('user',  ACL_READ);

        $status = $this->runMiddleware(
            $this->middleware, 'user'
        );

        $this->assertEquals($status, 200);
    }

    public function test_user_cannot_access() {
        $this->be($this->testUser);

        $this->testUser->grantPermission('user',  ACL_READ);

        $status = $this->runMiddleware(
            $this->middleware, 'page:1'
        );

        $this->assertEquals($status, 403);
    }

    protected function runMiddleware($middleware, $parameter)
    {
        try {
            return $middleware->handle(new Request(), function () {
                return (new Response())->setContent('<html></html>');
            }, $parameter)->status();
        } catch (UnauthorizedException $e) {
            return $e->getStatusCode();
        }
    }

}