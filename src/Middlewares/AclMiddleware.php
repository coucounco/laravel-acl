<?php
namespace rohsyl\LaravelAcl\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use rohsyl\LaravelAcl\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AclMiddleware
{
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }

        $roles = is_array($role)
            ? $role
            : explode('|', $role);

        $roles = array_map(function($item){
            $item = explode(':', $item);
            return [$item[0] => $item[1]];
        }, $roles);

        if (! Auth::user()->hasAclAny($roles)) {
            throw UnauthorizedException::forPermissions();
        }
        return $next($request);
    }
}