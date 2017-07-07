# Writing Views and Scripts

The CMS theme includes most scripts and shared stylesheets. Modules that provide their own styles must publish them, preferrably to a subdirectory of the `public/_cms` directory.

The blade directives described below may be used to include style and script assets.


## Blade Directives

The following blade directives are available in CMS templates:

- `@cms_script` / `@cms_endscript`  
    A block directive that will add content at the end of the HTML page. Does not auto-wrap in `<script>` tags, so don't forget to add those.
    
- `@cms_scriptonce` / `@cms_endscriptonce`  
    Like `cms_script` except the content will only be added once. If the exact same content is added more times, it will only be included once.
 
- `@cms_scriptasset($path)`  
    Adds a single `<script>` to the end of the HTML body. 

- `@cms_scriptassethead($path)`  
    Like `@cms_scriptasset`, but adds to the HTML `<head>` block instead. 
 
- `@cms_style($path, $type = null, $media = null, $rel = 'stylesheet')`  
    Adds a style `<link>` include to the HTML `<head>`.


## View Template

Any view may be used by (custom) CMS modules. To ensure that the view will be displayed correctly, use the following template blade code for reference:

```blade
@extends(cms_config('views.layout'))

@section('title', 'Your page title here')

@section('breadcrumbs')
    <ol class="breadcrumb">
        <li><a href="{{ cms_route(\Czim\CmsCore\Support\Enums\NamedRoute::HOME) }}">
                {{ ucfirst(cms_trans('common.home')) }}
            </a>
        </li>
        <li class="active">
            Your breadcrumb
        </li>
    </ol>
@endsection

@section('content')

    <div class="page-header">
        <h1>
            Your page title
        </h1>
    </div>
    
@endsection
```
