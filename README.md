# Laravel-Acl

[![Latest Stable Version](https://poser.pugx.org/rohsyl/laravel-acl/v/stable)](https://packagist.org/packages/rohsyl/laravel-acl)
[![Build Status](https://travis-ci.org/rohsyl/laravel-acl.svg?branch=master)](https://travis-ci.org/rohsyl/laravel-acl)
[![Total Downloads](https://poser.pugx.org/rohsyl/laravel-acl/downloads)](https://packagist.org/packages/rohsyl/laravel-acl)

This is a package that provide Access Control List for Laravel >5.8.

## Getting started

This package can be installed through Composer:
```
composer require rohsyl/laravel-acl
```
After installation you must perform these steps:

#### 1) Add the service provider in `config/app.php` file:

```
'providers' => [
    // ...
    rohsyl\LaravelAcl\ServiceProvider::class,
];
```


#### 2) Publish the laravel-acl config in your app
This step will copy the config file in the config folder of your Laravel App.

```
php artisan vendor:publish --provider="rohsyl\LaravelAcl\ServiceProvider"
```

When it is published you can manage the configuration of larvel-acl through the file in `config/acl.php`, it contains:

```
return [
    /**
     * Register every permissions here
     * key is the name of the permission
     * value is the id of the permission
     * Every permissions can have a different access level
     *    0 = ACL_NONE    -> no permission
     *    1 = ACL_READ    -> read permission
     *    2 = ACL_CREATE  -> create permission
     *    3 = ACL_UPDATE  -> update permission
     *    4 = ACL_DELETE  -> delete permission
     * Access level are incrmental. If user has a permission with an access level of ACL_CREATE
     * it means that the user can ACL_READ and ACL_CREATE
     */
    'permissions' => [
        'superadmin' => 0,
        'user' => 1,
        'group' => 2,
        'page' => 3,
    ],

    /**
     *
     */
    'roles' => [
        'admins' =>     "1",
        'user' =>   "4110",
    ],
    
    'model' => [
        // direct acl on user
        'user' => [
            /**
             * Enable acl for user
             */
            'enableAcl' => true,
            /**
             * The attribute where the acl is stored in the User model.
             * It's basically the name of the column in the database
             */
            'attributeName' => 'acl',
        ],
        // acl via groups of user
        // n-n relation between user and group
        'group' => [
            /**
             * Enable acl for group
             */
            'enableAcl' => false,
            /**
             * The name of the relationship from the User model to the "group" model
             */
            'relationship' => 'groups',
            /**
             * The attribute where the acl is stored in the "group" model.
             * It's basically the name of the column in the database
             */
            'attributeName' => 'acl',
        ]
    ]
];
```

### 3) Configure ACL for users

Optionally, you can enable direct acl on `User`

To enable this feature, you have to edit `model.user.enableAcl` 
it in the `config/acl.php` file and do the following instructions.

If you don't want direct acl on user, you can jump to the next chapter.

#### 3.1) Update the `User` model

Add the `UserAcl` trait in your `User` model.

```
namespace App;

...
use rohsyl\LaravelAcl\Traits\UserAcl;

class User extends Model {
    use UserAcl;

    ...    
}
```

#### 3.2) Add the acl column in the `users` table

Create a new migration file to update the `users` table

```
php artisan make:migration add_acl_in_users_table --table=users
```

And add the new column


```
<?php
   
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAclInUsersTable extends Migration
{
   /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
       Schema::table('users', function (Blueprint $table) {
            $table->text('acl')->nullable();
       });
   }

   /**
    * Reverse the migrations.
    *
    * @return void
    */
   public function down()
   {
       Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('acl');
       });
   }
}
```

> By default the name of this column is `acl`, but you can change it by updating `model.user.attributeName` in the `config/acl.php` file and in this migration file.

### 4) Configure ACL for groups

Optionnaly you can enable group acl.

To enable this feature, you have to edit `model.user.enableAcl` 
it in the `config/acl.php` file and do the following instructions.

If you don't want direct acl on user, you can jump to the next chapter.

#### 4.1) Set up database tables

To enable the acl for groups, you have to create some more tables or to update your existing tables.

You need a `groups` table and the pivot table `group_user` to create the ManyToMany relation. 
(it's okay if you have different naming)

If you need to create these tables please follow the chapter **4.1.1** else follow the **4.1.2**

##### 4.1.1) Create tables

> Do not create these tables if you already have similar grouping table with a ManyToMany relation to `User`.

Create a new migration file to create the `groups` table

```
php artisan make:migration create_groups_table
```

with the following content :

```
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $this->string('name');
            $this->text('acl')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
```

> The name of the `acl` column can be changed by updating `model.group.attributeName` in the `config/acl.php` file and in this migration file.


Add a new migration file to create the pivot table named `group_user` 

```
php artisan make:migration create_group_user_table
```

with the following content :

```
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_user', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('group_id');

            $table->foreign('user_id')
                ->references('id')->on('users');

            $table->foreign('group_id')
                ->references('id')->on('groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('group_user');
    }
}
```

That's all for database table creation.

##### 4.1.2) Update tables

Add the acl column in your grouping table.

Create a new migration file to update your grouping table.

> Be sure to have the right name for the table.

```
php artisan make:migration add_acl_in_groups_table --table=groups
```

And add the new column

```
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAclInUsersTable extends Migration
{
   /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
       Schema::table('groups', function (Blueprint $table) {
            $table->text('acl')->nullable();
       });
   }

   /**
    * Reverse the migrations.
    *
    * @return void
    */
   public function down()
   {
       Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('acl');
       });
   }
}
```

> By default the name of this column is `acl`, but you can change it by updating `model.group.attributeName` in the `config/acl.php` file and in this migration file.


That's all for database table update.

#### 4.2) Update the group model

Add the `GroupAcl` trait in your Group model. You also need to add the relationship to the `User` model

```
namespace App\Models;

...
use rohsyl\LaravelAcl\Traits\GroupAcl;

class Group extends Model
{
    use GroupAcl;

    ...

    public function users() {
        return $this->belongsToMany('App\User');
    }
}
```

#### 4.3) Update the `User` model

Add the relation into the `User` model. 

If your group table has a different name, it's not a problem. You just have to update `model.group.relationship` and set this name on the relation function
in the the `config/acl.php` file

```
namespace App;

class User extends Model {

    ...    

    public function groups() {
        return $this->belongsToMany('App\Group');
    }

}
```

### 5) Enjoy !

Everthing is ready, now jump to the documentation section to learn more about laravel-acl.

## Documentation

This chapter explain how laravel-acl works and describe the available tools (helpers, middleware, ...).

### Define permissions and roles

Permission and roles are managed in a ***hard coded*** way in the `config/acl.php` file. This choice was made to simplify the use 
and to avoid database query as much as possible.

#### Permissions

You can easly add permissions by adding a new entry in the `permissions` array in the `config/acl.php` file.

The key is the **name** of the permission and the value is the **identifier**

**It's mandatory to have the `superadmin` permission with the identifier set as `0`.**

You can manage every other permissions the way you want.

```
'permissions' => [
        'superadmin' => 0,
        'user' => 1,
        'group' => 2,
        'page' => 3,
        
        'run_page_import' => 9,
    ],
```

#### Roles 

A role is a preset of permissions. You can manage roles with the `roles` array in the `config/acl.php` file.

The key is the **name** of the role and the value is the **ACL**.

```
'roles' => [
    'admins' =>     "1",
    'user' =>   "4110",
],
```

#### ACL

What is the ACL ?

It's a string value that define the permissions of a user or a group. 

This value is stored by default in the `acl` column in the `users` table and in the group table.

Let's take the following ACL as a exemple.

```
"1000003410"
```

If we have defined the following permissions.


```
'permissions' => [
        'superadmin' => 0,
        'user' => 1,
        'group' => 2,
        'page' => 3,

        'run_page_export' => 8,        
        'run_page_import' => 9,
    ],
```

ACL are red from the **right** to the **left**.

What does each digit means ?
- The first digit `"0"` of the ACL `"1000003410"` represent the permission with the `0` identifier. In our case it's the `superadmin` permissions.
- The 2nd digit `"1"` represent the permission with the `1` identifier. (it's the `user` permission).
- The 4th digit `"3"` represent the `page` permission.
- The 8th `"0"` represent the `run_page_export` permission.
- The last digit (the 9th) `"1"` represent the `run_page_import` permission.

But, what are those values `"0"`, `"1"`, `"2"`, `"3"`, `"4"` on each digit ?

These values define the access level for the given permission

| Value | CONSTANT | Description |
| --- | --- | --- |
| `"0"` | `ACL_NONE` or `ACL_DENY` | no access |
| `"1"` | `ACL_READ` or `ACL_ALLOW` | the user (or group) has a read permission to something or is allowed to perform an action |
| `"2"` | `ACL_CREATE` | the user (or group) has the creation permission |
| `"3"` | `ACL_UPDATE` | the user (or group) has the update permission |
| `"4"` | `ACL_DELETE` | the user (or group) has the deletion permission |

So, to describe the ACL `"1000003410"`, the user has the following permissions/restrictions :
- The user is not a `superadmin`;
- The user has the read access on the `user` permission;
- The user has the delete acces on the `group` permission;
- The user has the update access on the `page` permission;
- The user can't perform `run_page_export`;
- And the user is allowed to perform `run_page_import`.

You know everything about the permissions, roles and ACL. Jump to the next chapter to learn how to grant and revoke permissions

### Grant and revoke permissions

#### To a User

How to give the superadmin permission to a user :
```
$user->grantPermission('superadmin', ACL_ALLOW);
$user->save();
```

> If you don't save, the permission will not persist in the database. 

How to give the read access to the `group` permission to a user :
```
$user->grantPermission('group', ACL_READ);
$user->save();
```

How to give the delete access to the `page` permission to a user :
```
$user->grantPermission('page', ACL_DELETE);
$user->save();
```

How to grant many permissions at once :
```
$user->grantPermissions([
    'page' => ACL_READ,
    'user' => ACL_UPDATE
 ]);
$user->save();
```

How to revoke a permission :
```
$user->revokePermission('user');
$user->save();
```

or you can also grant the `ACL_NONE` or `ACL_DENY` level.

```
$user->grantPermission('user', ACL_DENY);
$user->save();
```

How to revoke many permissions:
```
$user->revokePermissions(['user', 'group']);
$user->save();
```

How to revoke all permissions:
```
$user->revokePermissions();
$user->save();
```

#### To a group

It works the same way as with the user.

```
$group->grantPermission('user', ACL_READ);
$group->save();

$group->grantPermissions([
    'user' => ACL_READ,
    'page' => ACL_UPDATE,
    'run_page_export' => ACL_ALLOW
]);
$group->save();

$group->revokePermission('user');
$group->save();

$group->revokePermissions(['user', 'run_page_export']);
$group->save();
```

Now that you know how to grant and revoke permissions to a user or a group, you need to learn how to check permissions for a user.

### Checking access

This chapter describe every way to check if a user has the permissions to acces pages or perform actions.

#### Gate

You can use Gate facade provided by laravel.

```
Gate::allows('user', [ACL_CREATE]);
```


#### User model

You can use the  Laravel `can` method of the `User` to check a permission.

Check if the user can read page :
```
$hasPermission = auth()->user()->can('page', [ACL_READ]);
```
> It will return true if the user is able to read pages else it return false.

Check if the user can edit user in the context of the given group :
```
$group = Group::find(1);

$hasPermission = auth()->user()->can('page', [ACL_READ, $group]);
``` 
> It will return true only if the user has the acces granted by the given group else it return false.

> It's usefull to manage access to entity that are in relation with a specific group. 

Check if the user can update the page in the context of many groups : 
```
$group1 = Group::find(1);
$group2 = Group::find(12);

$hasPermission = auth()->user()->can('page', [ACL_READ, collect([$group1, $group2])]);
``` 
> The 2nd parameter (groups)" must be a  collection `Illuminate\Support\Collection`.

#### Middleware

You can use the `acl` middleware provided by laravel-acl to protect routes or a whole resource directly in the route files (`routes/web.php`).

Restrict the access to user with a given permission and level
```
Route::get('users/index', 'UserController@index')->name('users.index')->middleware('acl:user:1');
```
> This route will allow the acces only to user who have the `user` permission with the `ACL_READ` level.


Restrict the access to user with any of the given permission and level
```
Route::get('users/index', 'UserController@index')->name('users.index')->middleware('acl:user:1|group:1');
```
> This route will allow the acces only to user who have the `user` permission with the `ACL_READ` level or the `group` permission with the `ACL_READ` level.

#### Blade

It's usefull to be able to hide some buttons in your blade view. To achieve this, you can use some directive provided by Laravel.

##### The `@can` blade directive

```
@can('user', [ACL_READ])
    allow
@else
    denied
@endcan
```

It's the same as writing
```
@if(auth()->user()->can('user', [ACL_READ]))
    allow
@else
    denied
@endif
```

##### The `@cannot` blade directive

```
@cannot('user', [ACL_READ])
    denied
@else
    allow
@endcannot
```

##### The `@canany` blade directive

```
@canany(['user', 'page'], [ACL_READ])
    The user has page or user permisson with a read level
@elsecanany(['group'], ACL_READ)
    Or the user has the group permission with a read level
@else
    denied
@endcannot
```

#### Helper

##### Checking permissions

Checking if the user can read page
```
acl_has_permission($user, 'page', ACL_READ)
```

##### Retrieve permissions and roles

You can retrieve all permissions with the helper method :
```
$permissions = acl_permissions()
```

You can retrieve all roles with the helper method :
```
$roles = acl_roles()
```

### The `strict` option

Sometimes, you will probably want to check if the user have strictly a permission.

What does this mean ?

**Exemple:**

- `$user1` have the `superadmin` permission
- `$user2` have the `is_manager` permission

So when you check if the `$user1` have the `is_manager` permission : 

```
$user1->can('is_manager');
```

> this will return `true` even if he don't have the `is_manager` permission because he is `superadmin`.

It's possible to check permissions with the strict parameter.

```
$user1->can('is_manager', ACL_STRCT);
```

> this will return `false` even if the user is `superadmin`.


```
$user2->can('is_manager', ACL_STRCT);
```
> this will return `true` because `$user2` have the `is_manager` permission.