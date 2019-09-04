<?php
namespace rohsyl\LaravelAcl;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Access\Authorizable;

class AclRegistar
{
    /** @var \Illuminate\Contracts\Auth\Access\Gate */
    protected $gate;

    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    public function registerAcl() : bool {
        $this->gate->before(function (Authorizable $user, string $ability, $arguments) {
            if (method_exists($user, 'hasAcl')) {
                return $user->hasAcl($ability, $arguments) ?: null;
            }
        });
        return true;
    }
}