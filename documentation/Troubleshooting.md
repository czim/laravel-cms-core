# Troubleshooting


## Checklist

It's a good idea to check the following things when anything goes wrong. 

### For Any CMS

1. Run `php artisan vendor:publish` to make sure you have all the required migrations and assets.
2. Run `php artisan cms:migrate` to make sure any publish migrations for the CMS are applied.

### For CMSes using [Models Module](https://github.com/czim/laravel-cms-models)

1. Run `php artisan cms:models:clear` to force clearing out old configurations.  
    This will re-parse and re-cache the model configuration, on the next request that loads the CMS.
2. Run `php artisan cms:models:show --keys` to see which models are loaded.
3. Run `php artisan cms:models:show` and browse through the configuration data to see if anything looks out of order.  
    You can restrict the output to that for a specific model: `cms:models:show <key>`.


## Issues


### The CMS is slow

One way to speed up the CMS somewhat, is to enable menu caching.
You can do this by setting `cms-models.repository.cache` to `true`.
Note that this will require manually clearing the menu cache (using `php artisan cms:menu:clear`) whenever modules are
added or the menu configuration is altered.

If you are using the Models Module, make sure that you enable model configuration cache.
The cache configuration setting is `cms-models.repository.cache`, setting it `true` will cache the models.  
This is done according to your application (or CMS-specific) cache driver configuration.

If caching is disabled, the CMS will analyze the database structure, model class files and parse configuration files,
which can really slow things down as the amount of configured models increases.

### Menu items or menu layout changes don't show up

Make sure the CMS menu configuration cache is cleared.  
You can clear the menu cache by running `php artisan cms:menu:clear`.

It is easiest to leave the cache setting disabled while configuring the CMS.
You can do this by setting `cms-modules.menu.cache` to `false`, but note that this will make all CMS pageloads slower.


### Model configuration changes don't show up (Models Module)

Make sure the CMS model configuration cache is cleared.  
You can clear the models cache by running `php artisan cms:models:clear`.

It is easiest to leave the cache setting disabled while configuring the CMS.
You can do this by setting `cms-models.repository.cache` to `false`, but note that this will make all CMS pageloads slower.


### The base CMS URL (`<base URL>/cms`) fails to load (with a 404)
  
When running in a local development environment (using Laravel Valet or `php artisan serve`, for instance), 
be advised that some routes may not work depending on your `public` directory contents.

If you have a `cms` directory or file in Laravel's `public/` path, then `//<base URL>/cms/` may result in a 404,
ignoring Laravel's routing entirely. The solution is to make sure the public directory contains no such content,
or to change the base path for the CMS (using config key `cms-core.route.prefix`).

Note that this is not an issue with the CMS, and affects any Laravel routing where directory names in public match routes exactly.


### The CMS theme style is broken or interactive components (such as date pickers) don't work

Make sure you have run `php artisan vendor:publish` after adding the CMS Service provider to the `app.php` 
(or module service providers to the `cms-core.php`) configuration file.

CMS assets may have been altered between versions, and `vendor:publish` will not overwrite the existing contents.
Make sure to overwrite already published assets by adding the `--force` option:

```bash
php artisan vendor:publish --force --tag=assets --provider="Czim\CmsTheme\Providers\CmsThemeServiceProvider"
```

Be warned that forcing this will overwrite **translation** files published by the theme package. 
Omitting the `--tag` option will also cause the theme **configuration** file to be overwritten.
If you'd rather only update the public directory assets, simply delete the `public/_cms` directory and run an unforced `vendor:publish`.

For more information on publishing assets, see [the Laravel documentation](https://laravel.com/docs/5.3/packages#public-assets)


## Helpful Artisan Commands

If you're encountering problems due to modules or configuration, keep the following Artisan commands in mind:

Core:

- `cms:migrate:status`: check if CMS database migrations have all been run.
- `cms:modules:show`: show currently loaded modules.
- `cms:menu:show`: show currently loaded menu layout.

Models Module:

- `cms:models:clear`: clear cached model configuration.
- `cms:models:show`: show currently loaded and enriched model configuration.
