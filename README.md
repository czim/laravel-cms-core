# CMS for Laravel - Core

Modular CMS Core, which should manage the basics of configuring, accessing and deferring to modules.

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


[link-contributors]: ../../contributors
