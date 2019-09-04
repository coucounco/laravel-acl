<?php


namespace rohsyl\LaravelAcl\Traits;


trait GroupAcl
{
    use Acl;

    protected $acl_model = 'group';
}