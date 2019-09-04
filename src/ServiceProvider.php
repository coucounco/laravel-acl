<?php
namespace rohsyl\LaravelAcl;

use Illuminate\Support\ServiceProvider as SP;

class ServiceProvider extends SP
{

    public function boot(AclRegistar $aclRegistar)
    {
        $this->publishes([
            __DIR__ . '/../config/acl.php' => config_path('acl.php'),
            ], 'acl');


        $aclRegistar->registerAcl();


        $this->app->singleton(AclRegistar::class, function ($app) use ($aclRegistar) {
            return $aclRegistar;
        });
    }

    public function register()
    {
        //
    }
}
