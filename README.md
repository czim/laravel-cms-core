# CMS for Laravel - Core

Modular CMS Core, which should manage the basics of configuring, accessing and deferring to modules.

## Installation

### Middleware

To prevent conflicts with the Apps middleware, you may add the following method to your `App\Http\Kernel` class:

```php
    /**
     * Removes all global middleware registered.
     */
    public function removeGlobalMiddleware()
    {
        $this->middleware = [];
    }
```

While the CMS registers, it will automatically call this if it is available. This would make sure that neither the CMS nor the App end up with clashing middleware.

You do not need this if you have no global middleware defined; group or route middleware are not affected by this and won't be problematic in any case.


## Configuration

### Database

You can set a database `driver` and/or a `prefix`. 
If you set a driver, make sure that it exists in your application's `database.php` config file.

Note that if you set a driver that has a prefix and you add a CMS `prefix` aswell, that these will stack.

### Cache

If you want to use the `tags` to segregate your CMS cache content, make sure you use a tags-capable driver, such as Redis.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-contributors]: ../../contributors
