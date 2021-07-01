<?php


namespace rohsyl\LaravelAcl\Traits;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait GroupAcl
{
    use Acl;

    protected $acl_model = 'group';


    /**
     * Return true if the team has permission with the given parameters
     * @param $permission string The permission
     * @param $arguments array|mixed Argument
     * @return bool True if the access is granted
     */
    public function hasAcl(string $permission, $arguments) {
        // ignore if the permission doesn't exists in the configuration
        $config = $this->getPermissions();
        if(!isset($config[$permission])) return null;

        $level = is_array($arguments)
            ? $arguments[ACL_ARG_LEVEL] ?? ACL_NONE
            : $arguments;

        // strict is used to check if the user strictly have the given permission.
        // if the user don't have it, false will be returned even if the user is superadmin
        $strict = false;
        if($level === ACL_STRICT) {
            $strict = true;
            $level = ACL_ALLOW;
        }

        $permissionId = $config[$permission];

        $closure = function() use ($arguments, $permissionId, $level, $strict) {

            $groupAcl = null;

            // if the group acl is enabled
            if($this->getConfig('model.group.enableAcl')) {
                $groupAcl = $this->getAcl();
            }

            return $this->aclIsPermissionGranted($permissionId, $groupAcl, $level);
        };

        $key = $this->getCacheKey($permissionId, $level, $this, $strict);
        $cacheExpirationTime = config('acl.cache.expiration_time') ?? 60 * 60 * 24 * 5;

        // Cache tags are not supported when using the file or  database cache drivers.
        //$driver = config('cache.default');
        //if($driver == 'file' || $driver == 'database') {
        if(config('acl.cache.enable')) {
            return Cache::remember($key, $cacheExpirationTime, $closure);
        }
        else {
            return $closure();
        }
        //}
        //else {
        //    return Cache::tags(['laravel-acl', 'laravel-acl-user-'.$this->id])->remember($key, $cacheExpirationTime, $closure);
        //}
    }
}