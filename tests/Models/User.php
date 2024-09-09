<?php
namespace coucounco\LaravelAcl\Test\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use coucounco\LaravelAcl\Traits\UserAcl;

class User extends Authenticatable
{
    use Notifiable;
    use UserAcl;

    protected $fillable = [
        'email',
        'acl'
    ];

    public $timestamps = false;

    public function groups() {
        return $this->belongsToMany('coucounco\LaravelAcl\Test\Models\Group');
    }
}
