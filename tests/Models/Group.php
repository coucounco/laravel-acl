<?php
namespace rohsyl\LaravelAcl\Test\Models;

use Illuminate\Database\Eloquent\Model;
use rohsyl\LaravelAcl\Traits\GroupAcl;

class Group extends Model
{
    use GroupAcl;

    protected $fillable = [
        'name',
        'acl'
    ];

    public $timestamps = false;

    public function users() {
        return $this->belongsToMany('rohsyl\LaravelAcl\Test\Models\User');
    }
}