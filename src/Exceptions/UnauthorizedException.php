<?php


namespace rohsyl\LaravelAcl\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedException extends HttpException
{
    public static function notLoggedIn(): self
    {
        return new static(403, 'User is not logged in.', null, []);
    }

    public static function forPermissions(): self
    {
        return new static(403, 'User does not have the right permissions.', null, []);
    }
}