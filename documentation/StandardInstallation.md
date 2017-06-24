# Standard CMS Installation

Here's a guide for a standard complete CMS installation.

This includes the setup of the following:

- The CMS core
- The Authentication module
- The models module for managing data described by Eloquent models
- A standard ACL module for managing users and permissions
- The default bootstrap theme
- A file uploader module, for nicer AJAX file uploads.


## Core Installation

1. Add the composer packages:

In **Laravel 5.3**: 

```bash
# The base components
composer require czim/laravel-cms-core:~1.3.0 czim/laravel-cms-auth:~1.3.0 czim/laravel-cms-theme
# Standard modules
composer require czim/laravel-cms-models:~1.3.0 czim/laravel-cms-acl-module czim/laravel-cms-upload-module
```

In **Laravel 5.4**:

```bash
# The base components
composer require czim/laravel-cms-core:~1.4.0 czim/laravel-cms-auth:~1.4.0 czim/laravel-cms-theme
# Standard modules
composer require czim/laravel-cms-models:~1.4.0 czim/laravel-cms-acl-module czim/laravel-cms-upload-module
```


2. Add the Core service provider to `app.php` providers array:

```php
Czim\CmsCore\Providers\CmsCoreServiceProvider::class,
```

3. Publish the `cms-core.php` and `cms-modules.php` config files:

```bash
php artisan vendor:publish
```

## Modules Installation

1. Add the modules to the `cms-modules.php` config:

```php
    'modules' => [
        Czim\CmsModels\Modules\ModelModuleGenerator::class,
        Czim\CmsAclModule\AclModule::class,
        Czim\CmsUploadModule\Modules\UploadModule::class,
    ],
```

2. Add the service providers for the modules to the `cms-core.php` config:

```php
    'providers' => [
        // ...
                
        Czim\CmsModels\Providers\CmsModelsServiceProvider::class,
        Czim\CmsAclModule\Providers\CmsAclModuleServiceProvider::class,
        Czim\CmsUploadModule\Providers\CmsUploadModuleServiceProvider::class,
        
        // ...
    ],
```

Make sure that the route related service providers come after the additions.

3. Publish the configuration files for the modules, making use of the service providers that were just defined:
 
```bash
php artisan vendor:publish
```

## Models Module Configuration

1. Add any models that you want to make editable through the CMS to the `cms-models.php` config:
 
```php
    'models' => [
        App\Models\YourModel::class,
        App\Models\AnotherModel::class,
        // ...
    ],
```

2. Specify configuration for models.

Add model configuration files with names identical to the models in the `App\Cms\Models\` namespace.  
This only needs to be done if you want to overrule CMS defaults for a given model.

You can find [more information about model configuration here](https://github.com/czim/laravel-cms-models/blob/master/documentation/ModelConfiguration.md).  


## Database

1. Run migrations for the CMS:

```bash
php artisan cms:migrate
```

## Set up an admin account

1. Create a new admin user account for the CMS:

```bash
php artisan cms:user:create someusername@domain.ext --admin
```

This user will have unrestricted access, and can be used to set up other user accounts and roles, and assign permissions.

After this, you can log in to your CMS at `<your app URL>/cms`.


## Optional Tweaks

1. Set the default action on CMS login.

Unless you want to show an empty page to logged in users, you can set the default action.
 
In `cms-core.php`, set the `route.default` key: 

```php
    'route' => [
 
         // ...
 
         'default' => Your\Controller\Here::class . '@index',
     ],
```

Be advised that this action should be accessible/usable by all logged in users.


## Further information

Further information about configuring and tweaking the CMS:

- [Model configuration files](https://github.com/czim/laravel-cms-models/blob/master/documentation/ModelConfiguration.md)
- [Models module strategies](https://github.com/czim/laravel-cms-models/blob/master/documentation/Strategies.md)
