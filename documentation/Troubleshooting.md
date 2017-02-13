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

### The base CMS URL (`<base URL>/cms`) fails to load (with a 404).
  
When running in a local development environment (using Laravel Valet or `php artisan serve`, for instance), 
be advised that some routes may not work depending on your `public` directory contents.

If you have a `cms` directory or file in Laravel's `public/` path, then `//<base URL>/cms/` may result in a 404,
ignoring Laravel's routing entirely. The solution is to make sure the public directory contains no such content,
or to change the base path for the CMS (using config key `cms-core.route.prefix`).

Note that this is not an issue with the CMS, and affects any Laravel routing where directory names in public match routes exactly.


## Helpful Artisan Commands

If you're encountering problems due to modules or configuration, keep the following Artisan commands in mind:

Core:

- `cms:migrate:status`: check if CMS database migrations have all been run.
- `cms:modules:show`: show currently loaded modules.

Models Module:

- `cms:models:clear`: clear cached model configuration.
- `cms:models:show`: show currently loaded and enriched model configuration.
