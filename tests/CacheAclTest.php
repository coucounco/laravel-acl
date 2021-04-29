<?php
namespace rohsyl\LaravelAcl\Test;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class CacheAclTest extends TestCase
{
    use DatabaseMigrations;

    public function test_cache() {;

        $this->testUser->grantPermission('user', ACL_READ);

        $cacheKey = config('acl.cache.key') ?? 'laravel-acl_';
        $permissionId = config('acl.users.permissions.user');
        $level = ACL_READ;
        $key = $cacheKey . $this->testUser->id . ':' . $permissionId . ':' . $level;

        Cache::shouldReceive('remember')
            ->once()
            ->with($key, 432000, Closure::class)
            ->andReturn(1);

        $this->testUser->can('user', ACL_READ);
    }

    public function test_cache_flush() {
        Cache::shouldReceive('flush');
        $this->testUser->grantPermission('user', ACL_READ);
    }

}