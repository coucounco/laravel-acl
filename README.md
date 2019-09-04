# Laravel-Acl

Access Control List for Laravel >5.8.

## Getting started

This package can be installed through Composer:
```
composer require rohsyl/laravel-acl
```
After installation you must perform these steps:

#### 1) add the service provider in `config/app.php` file:

```
'providers' => [
    // ...
    rohsyl\LaravelAcl\ServiceProvider::class,
];
```


#### 2) publish laravel-acl in your app
This step will copy the config file in the config folder of your Laravel App.

```
php artisan vendor:publish --provider="rohsyl\LaravelAcl\ServiceProvider"
```

When it is published you can manage the configuration of LaraUpdater through the file in `config/acl.php`, it contains:

```
[
    
]
```

## Documentation

```
namespace App;

use rohsyl\LaravelAcl\Traits\HasAcl;

class User extends Model {
    use HasAcl;
}
```