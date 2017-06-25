# CMS Modules and Components

This is a listing of all known publically available components and modules for this CMS.

If you have created or know about a package that should be listed here, I'd appreciate getting a heads-up, or even a pull request to add it to this page.


## Components

Components, unlike modules, are required for the CMS to work. 
These are necessary but interchangeable add-ons to the core.
 
The main reason these are split off as separate packages, is to make it easier to replace or extend them with custom versions.

- [Authentication component](https://github.com/czim/laravel-cms-auth)  
    Authentication, user and permission support for web and API access.

- [Theme component](https://github.com/czim/laravel-cms-theme)  
    The look and feel of the CMS, as well as javascripts and general assets.


## Available modules

Modules are optional and may be added to the CMS as needed.


- The default [ACL Module](https://github.com/czim/laravel-cms-acl-module)  
    A very simple user, role & permission management module.

- The default [Models Module](https://github.com/czim/laravel-cms-models)  
    Allows extensive management of Eloquent model data.  
    For simple applications, this may be all you need for a complete CMS. 

Modules may require special configuration and setup, and have their own dependencies.
Please check each module's documentation for further information.


### Making Custom Modules

The above list is not exhaustive, if only because making custom modules is encouraged.

For more information, see [creating custom modules](development/CreatingModules.md).
