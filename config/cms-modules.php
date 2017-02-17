<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    |
    | The CMS is set up to be modular. Custom modules may be defined here.
    | This list may include FQNs for implementations of either the
    | ModuleInterface or the ModuleGeneratorInterface.
    |
    | The order in which modules will be processed for dispay (in the menu)
    | is determined by their order in the list. Any modules not present in
    | this list will be drawn below/after these, in their natural order.
    |
    */

    'modules' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    |
    | Adding modules may enable default menu presence, but this depends
    | on the module content. To customize (or disable) menu presence,
    | you can set per-module menu configuration in this section.
    |
    */

    'menu' => [

        // Group structure (listed by key) that modules can hook into.
        // This will define a menu structure that may be nested as follows:
        //
        //      'some-group-key' => [
        //          'label'    => 'Display Name of Group',
        //          'children' => [
        //              'some-other-group-key' => [ 'name' => 'Another Display Name' ]
        //          ]
        //      ]

        'groups' => [
        ],

        // Per module menu presence configuration.
        //
        // The order in which the modules are listed will be used to order the presences,
        // whether they are grouped or not.
        //
        //      'module-slug' => [
        //          // to move the module's menu presence to a group:
        //          'group' => 'some-group-key',
        //          // to override the module's default presence configuration entirely:
        //          'presence' => [ ... ]
        //      ]

        'modules' => [
        ],

    ],

];
