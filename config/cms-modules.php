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
    | The menu is composed of module presences, which may be configured,
    | ordered, or even assigned in a specific nested, grouped layout.
    |
    */

    'menu' => [

        // A menu layout with groups may be defined here, along with the order in which they appear.
        // Groups are represented by arrays. Menu items for modules are referenced by module key.
        //
        // Example:
        //
        //  'layout' => [
        //      [
        //          'label' => 'Group label for display',
        //          'children' => [
        //              'another.module-key',
        //              'yet-another.module-key',
        //          ]
        //      ],
        //      'some.module-key',
        //  ]

        'layout' => [
        ],

        // Module menu presence configuration and default order of appearance.
        //
        // Entries in this array may be either module keys, or key value pairs with
        // further configuration for a module's menu presence.
        //
        // Note that the order of appearance defined in the layout key above overrules
        // the order of the .
        //
        // Example:
        //
        //  'modules' => [
        //      'first-module-key',
        //      'second-module-key',
        //      'third-module-key' => [
        //          'presence' => [
        //              ... (full menu presence configuration)
        //          ]
        //      ],
        //      'some.module-key',
        //  ]

        'modules' => [
        ],

    ],

];
