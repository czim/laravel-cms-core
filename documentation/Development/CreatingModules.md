# Creating Custom Modules

## Modules

A single module class may be registered with the CMS.


### Creating Modules

1. Create a new module class.  
    This must implement `Czim\CmsCore\Contracts\Modules\ModuleInterface`.  
    It is advisable to set a key that is unlikely to cause conflicts with other modules or generators.
    
2. Create service providers as needed.  
    This is not required, but is highly recommended if your module requires provision that is not needed for non-CMS requests.

### Installing Modules

3. Register the module in the `cms-modules.php` config.  
    Add the fully qualified namespace for the module class to the `cms-modules.modules` array.
    
4. If relevant, add service providers to the `cms-core.php` config.  
    Add the fully qualified namespaces for any service providers to the `cms-core.providers` array.
    Be sure to add this *after* the normal core providers, and *before* the route providers.


## Module Generators

Instead of coding concrete module classes, it is possible to register a module generator with the CMS.
This may generate any number of modules procedurally, which will then each be registered with the CMS.

For an example of a module generator, see the [models module generator](https://github.com/czim/laravel-cms-models/blob/master/src/Modules/ModelModuleGenerator.php).

### Creating Module Generators

1. Create a new module generator class.  
    This must implement `Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface`.  
    It is advisable to set a key that is unlikely to cause conflicts with other modules or generators.

2. Create service providers as needed.  
    This is not required, but is highly recommended if your module requires provision that is not needed for non-CMS requests.

### Installing Module Generators

The proces is the same as for installing modules as described above.
The only difference is that the generator FQN is registered in `cms-modules.modules` instead of a module class.


## A Warning About Using `associatedClass`

If you're using the [models module](https://github.com/czim/laravel-cms-models), 
avoid setting the `associatedClass` module property to an Eloquent model.
This package relies on looking up (generated) modules by their Eloquent model classname.
As long as there are duplicates, you should be fine. 
