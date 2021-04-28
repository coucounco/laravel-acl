<?php
namespace rohsyl\LaravelAcl;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as SP;
use rohsyl\LaravelAcl\Middlewares\AclMiddleware;

class ServiceProvider extends SP
{

    public function boot(AclRegistar $aclRegistar)
    {
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
            __DIR__ . '/../config/acl/users.php' => config_path('acl/users.php'),
        ], 'config');

        if (app()->version() >= '5.5') {
            $this->registerMacroHelpers();
        }

        $aclRegistar->registerAcl();

        $this->app['router']->aliasMiddleware('acl', AclMiddleware::class);

        $this->app->singleton(AclRegistar::class, function ($app) use ($aclRegistar) {
            return $aclRegistar;
        });
    }

    public function register()
    {
        //
    }

    protected function registerMacroHelpers()
    {
        Route::macro('acl', function ($roles = []) {
            if (! is_array($roles)) {
                $roles = [$roles];
            }
            $roles = implode('|', $roles);
            $this->middleware("role:$roles");
            return $this;
        });
    }
}
