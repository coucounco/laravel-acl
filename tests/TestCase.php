<?php

namespace rohsyl\LaravelAcl\Test;

use Illuminate\Database\Schema\Blueprint;
use rohsyl\LaravelAcl\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use rohsyl\LaravelAcl\Test\Models\Group;
use rohsyl\LaravelAcl\Test\Models\User;

class TestCase extends Orchestra
{
    protected $testUser;

    protected $testGroups;

    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class
        ];
    }


    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);
        $this->setUpAclConfig($this->app);

        $this->testUser = User::first();
        $this->testGroups = Group::all();
    }

    /**
     * Set up the acl config.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpAclConfig($app) {
        $app['config']->set('acl.permissions', [
            'superadmin' => 0,
            'user' => 1,
            'group' => 2,
            'page' => 3,
            'module' => 9,
        ]);
        $app['config']->set('acl.model', [
            // direct acl on user
            'user' => [
                'enableAcl' => true,
                'attributeName' => 'acl',
            ],
            // acl via groups of user
            // n-n relation between user and group
            'group' => [
                'enableAcl' => true,
                'relationship' => 'groups',
                'attributeName' => 'acl',
            ]
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('view.paths', [__DIR__.'/resources/views']);
    }

    /**
     * Set up the database.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function setUpDatabase($app)
    {
        $app['db']->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->text('acl')->nullable();
        });
        $app['db']->connection()->getSchemaBuilder()->create('groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('acl')->nullable();
        });
        $app['db']->connection()->getSchemaBuilder()->create('group_user', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('user_id');

            $table->foreign('group_id')
                ->references('id')->on('groups');

            $table->foreign('user_id')
                ->references('id')->on('users');
        });

        User::create(['email' => 'test@user.com']);
        Group::create(['name' => 'Team 1']);
        Group::create(['name' => 'Team 2']);
        Group::create(['name' => 'Team 3']);
    }
}