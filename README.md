
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://travis-ci.org/czim/laravel-cms-core.svg?branch=master)](https://travis-ci.org/czim/laravel-cms-core)
[![Coverage Status](https://coveralls.io/repos/github/czim/laravel-cms-core/badge.svg?branch=master)](https://coveralls.io/github/czim/laravel-cms-core?branch=master)


# CMS for Laravel - Core

So you're looking for a Laravel CMS, and ...

- you don't want to mold your app to conform to the CMS,
- you need it to handle Eloquent models that are *Translatable*, *Listified*, or have your custom traits without any problems,
- you want to offer your clients interfaces that can always be molded to their eccentric preferences,
- you want it to be easily set up in both fresh applications as well as time-worn codebases,
- you don't want to be stuck with some standard theme,
- and you don't want your CMS configuration to be outside of your project's code repository.

Look no further. This CMS was developed with all this in mind.
 
## Version Compatibility

 Laravel             | Package 
:--------------------|:--------
 5.3.x               | 1.3.x
 5.4.x               | 1.4.x
 5.5.x               | 1.5.x
 
A note on Laravel 5.5 support: The Core and Auth components are fully compatible and tested to work. The theme and ACL module are not fully tested, but should work without problems. The Models module has been tested for ~85% and will work.
 
Also note that Stapler is broken since Laravel 5.4, but [may be fixed](https://github.com/CodeSleeve/laravel-stapler/issues/118). In 5.5 Stapler has [an additional issue](https://github.com/CodeSleeve/stapler/issues/186).  
Alternatively, you may use [Laravel Paperclip](https://github.com/czim/laravel-paperclip), that offers (at least) the same functionality.


## A Framework CMS
 
The core concept behind this CMS: it is a **framework**.

What makes Laravel great? 
The fact that it is *easy* to do simple things with it, and it is *powerful* and *flexible* enough to let you do whatever you want with it.

A framework offers developers a tool that lets them work on *business logic* rather than the boring, repetitive basics. 
It is a tool that derives its value from letting programmers write code and take control wherever and however they want.

This CMS is similar to most Laravel CMSes in that it offers a convenient way to quickly set up a user-friendly tool for managing data. 
Where it differs is in being structured through-and-through as a framework: in a way that lets developers modify its behavior by writing code. 
All parts of this CMS are written under the assumption that someone may want to, and should be able to, change the way things work.
    
It does this by using Laravel's service container, abstract bindings that you can replace using configuration files, and strategy classes that may be easily swapped out. 
At the top level, the CMS is modular and component-based, making it easy to add, remove or fork and replace any part of it.   


## Core

This is the Modular CMS Core, which manages the basics of configuring, accessing and deferring to modules.  

The core offers:

- Module management, including module based routing and authorization,
- CMS provision, loading required service providers,
- The basics for a dedicated OAuth2 API for the CMS,
- Segregated database migrations and the commands to manage them,
- The basics for user notification,
- Some basic debugging tools to help analyze loaded modules.


## Heads-up / Disclaimer

This project is currently under heavy development, but it is ready for production environments. It is only recommended for experienced programmers, however.   
Feedback is welcome, as always.


## Documentation

The CMS core is not a stand-alone package. Some components and modules are required to start using the CMS.

### Where Do I Start?

Beyond a very basic setup, this CMS has a bit of a learning curve. 
Here are some suggested approaches to getting started; pick any that best suit your needs.
  
- Install [a pre-configured demo Laravel application](https://github.com/czim/laravel-cms-example) with the CMS fully installed.  
    If you're just curious what a basic installation of the CMS can do or what it looks like, 
    this showcase is a good place to start. 
    Just check out the repository and follow a few simple steps to get the example running locally.

- Follow [a step-by-step installation guide](documentation/StandardInstallation.md) to try the CMS out in your own Laravel application.  
    This can be a freshly installed copy of Laravel, or a pre-existing application in any stage of development.
    This CMS is designed to be mostly a drop-in solution. 

- Explore [available components and modules](documentation/Modules.md).   

There is currently no public live example online. The quickest way to get a peek at this CMS is the first approach listed above.


### Reference Material

You can find more information here:

- [Installing and setting up standard modules](documentation/Modules.md) for the CMS.
- [The way the menu works](documentation/Menu.md) and how you can customize it.
- [Resources for developers](documentation/Development.md) customizing and extending the CMS.


## Troubleshooting

If you encounter problems, please first [consult the troubleshooting section](documentation/Troubleshooting.md).

When does not help, posting an issue report is much appreciated.


## API Documentation

The documentation for the API endpoints provided by the core may be found here:  
[czim.github.io/laravel-cms-core](https://czim.github.io/laravel-cms-core).


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

[ico-version]: https://img.shields.io/packagist/v/czim/laravel-cms-core.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/czim/laravel-cms-core.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/czim/laravel-cms-core
[link-downloads]: https://packagist.org/packages/czim/laravel-cms-core
[link-author]: https://github.com/czim
[link-contributors]: ../../contributors
