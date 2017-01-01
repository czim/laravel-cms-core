# CMS Modules and Components


## Components

Components are required for the CMS to work. 
The main reason these are split off from the core is to make it easier to replace or extend them with custom versions. 

- [Authentication component](https://github.com/czim/laravel-cms-auth)  
    Authentication, user and permission support for web and API access.

- [Theme component](https://github.com/czim/laravel-cms-theme)  
    The look and feel of the CMS, as well as javascripts and general image assets.


## Available modules

Modules are optional and may be added to the CMS as needed.


- [ACL Module](https://github.com/czim/laravel-cms-acl-module)  
    A very simple user, role & permission management module.

- [Models Module](https://github.com/czim/laravel-cms-models)
    Allows extensive management of Eloquent model data.  
    For simple applications, this may be all you need for a complete CMS. 

Some modules may require eachother by design, or require special configuration and setup.
Please check each module's documentation for further information.


### Making Custom Modules

The above list is not exhaustive, if only because making custom modules is encouraged.

For more information, see [creating custom modules](CreatingModules.md).
