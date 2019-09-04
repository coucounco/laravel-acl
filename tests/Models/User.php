<?php
namespace rohsyl\LaravelAcl\Test\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use rohsyl\LaravelAcl\Traits\UserAcl;

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
        return $this->belongsToMany('rohsyl\LaravelAcl\Test\Models\Group');
    }
}