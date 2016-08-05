FORMAT: 1A
HOST: meta

# Meta

CMS Meta information API endpoints.

# Group Version

CMS main component versions.

# Versions [/versions]

## Retrieve list of versions [GET]

Retrieves version strings (should be semantic) for the CMS core and its main components.

+ Response 200 (application/json)

        {
            "data": [
                {
                    "id": 1,
                    "name": "core",
                    "version": "0.0.1"
                },
                {
                    "id": 2,
                    "name": "auth",
                    "version": "0.0.1"
                },
                {
                    "id": 3,
                    "name": "module-manager",
                    "version": "0.0.1"
                }
            ]
        }


# Group Modules 

## Modules [/modules]

### Retrieve all modules [GET]

Retrieves a list of information about all loaded modules.

+ Response 200 (application/json)

        {
           "data": [
               {
                   "id": "test-module",
                   "name": "Testing Module",
                   "version": "0.0.1",
                   "class": "App\Models\SomeClass"
               },
               {
                   "id": "acl-simple",
                   "name": "Access Control Management",
                   "version": "0.0.1",
                   "class": null
               }
           ]
        }


## Single Module [/modules/{moduleKey}]

+ Parameters
    + moduleKey: some-module-key (required, string) - Key of the module

### Retrieve a module [GET]

Retrieves information about a single module by its unique key.

+ Response 200 (application/json)

    + Attributes (object)
        + key: acl-simple (string, required)
        + name: Simple ACL Module (string)
        + version: 0.0.1 (string, required)
        + class: App\Models\SomeClass (string) - The class that is associated with the module

    + Body

            {
                "data": {
                    "key": "acl-simple",
                    "name": "Access Control Management",
                    "version": "0.0.1",
                    "class": null
                }
            }


# Group Menu

## Menu structure [/menu]

### Retrieve full menu structure [GET]

Retrieves full menu structure trees separated into `groups` and `ungrouped` categories.

The tree structure consists of menu presences. 
If a presence has `type` of `group`, it may have children.

+ Response 200 (application/json)

    + Attributes (object)
        + groups (array[Menu Presence])
        
            The menu items that are deliberately configured to be grouped. 
            A nested array with Menu Presence objects (that blueprint cannot parse yet).
            
        + ungrouped (array[Menu Presence])
        
            The menu items that are left ungrouped. These may be added to its own default group, or listed separately.
            Note that these *will* still have their own individual module-defined nested structure.
            A nested array with Menu Presence objects (that blueprint cannot parse yet)

    + Body
    
            {
                "groups": [
                    {
                        "id": "some-group",
                        "type": "group",
                        "label": "group display name",
                        "image": null,
                        "action": null,
                        "parameters": [],
                        "permissions": null,
                        "children": [
                            {
                                "id": null,
                                "type": "action",
                                "label": "Testing action",
                                "image": null,
                                "action": "testing::index",
                                "parameters": [],
                                "permissions": "do.something",
                                "children": []
                            }
                        ]
                    }
                ],
                "ungrouped": [
                    {
                        "id": "simple-acl",
                        "type": "group",
                        "label": "Access Control",
                        "image": null,
                        "action": null,
                        "parameters": [
                        ],
                        "permissions": null,
                        "children": [
                            {
                                "id": "simple-acl-users",
                                "type": "action",
                                "label": "Users",
                                "image": null,
                                "action": "cms::acl.users.index",
                                "parameters": [],
                                "permissions": "acl.users.show",
                                "children": []
                            },
                            {
                                "id": "simple-acl-create-user",
                                "type": "action",
                                "label": "New User",
                                "image": null,
                                "action": "cms::acl.users.create",
                                "parameters": [],
                                "permissions": "acl.users.create",
                                "children": []
                            },
                            {
                                "id": "simple-acl-roles",
                                "type": "action",
                                "label": "Roles",
                                "image": null,
                                "action": "cms::acl.roles.index",
                                "parameters": [],
                                "permissions": "acl.roles.show",
                                "children": []
                            }
                        ]
                    }
                ]
            }


# Data Structures

## Menu Presence (object)
+ id: some menu presence group (string)
    
    Uniquely identifying key or id for the presence.
    This is mainly useful for groups or other presences that should set a HTML tag ID attribute value.

+ type: action (string, required) - The type of presence (group, action, etc.)
+ label: Action name (string) - Display label for the presence (may be translated)
+ image: some-icon (string) - Free string value that should refer to an icon or image to display for the presence
+ action: cms::some.action (string) - The action or link to follow on click
+ parameters (array) - Optional parameters that should be used for the action (f.i.: route parameters)
+ permissions: some.item.show,some.item.delete (array[string]) - The permissions required for users to use or see this presence
+ children (array[Menu Presence]) - Child presences (each instances of this same object structure)
