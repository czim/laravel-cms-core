# CMS for Laravel - Core

So you're looking for a Laravel CMS, and ...

- you don't want to mold your app to conform to the CMS,
- you need it to handle Eloquent models that are *Translatable*, *Listified*, or have your custom traits without any problems,
- you want to offer your clients interfaces that can always be molded to their eccentric preferences,
- you want it to be easily set up in both fresh applications as well as time-worn codebases,
- you don't want to be stuck with some standard theme,
- and you don't want your CMS configuration to be outside of your project's code repository.

Look no further. This CMS was developed with all this in mind.
 
 
## A Framework CMS
 
The core concept behind this CMS: it is a **framework**.

What makes Laravel great? 
The fact that it is *easy* to do simple things with it, and it is *powerful* and *flexible* enough to let you do whatever you want with it.

A framework offers developers a tool that lets them work on *business logic* rather than the boring, repetitive basics. 
It is a tool that derives its value from letting programmers write code and take control wherever and however they want.

This CMS is similar to most Laravel CMSes in that it offers a convenient way to quickly set up a user-friendly tool for managing data. 
Where it differs is in being structured through-and-through as a framwork: in a way that lets developers modify its behavior by writing code. 
All parts of this CMS are written under the assumption that someone may want to, and should be able to, change the way things work.
    
It does this by using Laravel's service container, abstract bindings that you can replace using configuration files, and strategy classes that may be easily swapped out. 
At the top level, the CMS is modular and component-based, making it easy to add, remove or fork and replace any part of it.   


## Core

This is the Modular CMS Core, which manages the basics of configuring, accessing and deferring to modules.  


## Documentation

The CMS core is not a stand-alone package. Some components and modules are required to start using the CMS.

You can find more information here:

- [Installing and setting up modules](documentation/Modules.md) for the CMS
- [Standard installation guide](documentation/StandardInstallation.md)

For an example installation of this CMS, check out the [Laravel CMS Example repository](https://github.com/czim/laravel-cms-example). 


## API Documentation

The documentation for the API endpoints provided by the core may be found here: https://czim.github.io/laravel-cms-core


## Version Compatibility

 Laravel  | Package 
:---------|:--------
 5.1.x    | 0.9.x
 5.2.x    | 0.9.x
 5.3.x    | 1.3.x

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
