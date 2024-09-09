<?php
namespace coucounco\LaravelAcl\Test\Models;

use Illuminate\Database\Eloquent\Model;
use coucounco\LaravelAcl\Traits\GroupAcl;

class Group extends Model
{
    use GroupAcl;

    protected $fillable = [
        'name',
        'acl'
    ];

    public $timestamps = false;

    public function users() {
        return $this->belongsToMany('coucounco\LaravelAcl\Test\Models\User');
    }
}
