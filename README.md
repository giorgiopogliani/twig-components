# Twig components extension

[![Latest Version on Packagist](https://img.shields.io/packagist/v/performing/twig-components.svg?style=flat-square)](https://packagist.org/packages/performing/twig-components)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/giorgiopogliani/twig-components/Tests)](https://github.com/giorgiopogliani/twig-components/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/performing/twig-components.svg?style=flat-square)](https://packagist.org/packages/performing/twig-components)

This is a twig extension for automatically create components as tags. The name of the tag is based on the filename and the path. This is highly inspired from laravel blade components.  

## Installation

You can install the package via composer:

```bash
composer require performing/twig-components
```

## Setup
**Update!** You can use just this line of code to setup the extension, this will enable: 
- components from the specified directory
- safe filters so no need to use `|raw`
- html-like as x-tags
```php
\Performing\TwigComponents\Setup::init($twig, '/relative/directory/to/components');
```

You want can still use the old method:
```php
$extension = new \Performing\TwigComponents\ComponentExtension('/relative/twig/components/directory');
$twig->addExtension($extension); 
```

## Syntax

You can use a component `component-name.twig` like this anywhere in your templates. 
```twig
{% x:component-name with {any: 'param'} %}
    <strong>Any Content</strong>
{% endx %}
```

You can also reach for components files that are in **sub-folders** with a dot-notation syntax. For example, a component at `/components/button/primary.twig` would become
```twig
{% x:button.primary with {any: 'param'} %}
    <strong>Any Content</strong>
{% endx %}
```

## Components
You can create a file in the components directory like this:
```html
{# /components/button.twig #}
<button {{ attributes.merge({ class:'text-white rounded-md px-4 py-2' }) }}>
  {{ slot }}
</button>
```

Next, use the new component anywhere in your templates with this syntax or with x-tags.
```twig
{# /index.twig #}
{% x:button with {class:'bg-blue-600'} %}
  <span class="text-lg">Click here!</span>
{% endx %}

{# or #}

<x-button class='bg-blue-600'>
  <span class="text-lg">Click here!</span>
</x-button>
```

The output generated will be like this.
```html
<button class="bg-blue-600 text-white rounded-md px-4 py-2">
  <span class="text-lg">Click here!</span>
</button>
```

## Features

### Slots (0.2.0)
```twig
{% x:card %}
   {% slot:title %} Some Title {% endslot %}
    
    Some content {# normal slot variable #}
   
   {% slot:buttons %}  
       {% x:button.primary %} Submit {% endx %}
   {% endslot %}
{% endx %}
```

### Pro Tip (VSCode)
Add this your user twig.json snippets 
```
"Component": {
  "prefix": "x:",
  "body": [
    "{% x:$1 %}",
    "$2",
    "{% endx %}",
  ],
  "description": "Twig component"
}
```

### X Tags (0.3.0)
Now, you can enable `<x-tags />` for your twig components, here an example: 
```html
<x-button>
    <x-slot name="icon">
        <!-- my icon -->
    </x-slot>
    Submit
</x-button>
```

You can pass attributes like this:
```html
<x-button 
    :any="'evaluate' ~ 'twig'"
    other="{{'this' ~ 'works' ~ 'too'}}" 
    another="or this"
    not-this="{{'this' ~ 'does'}}{{ 'not work' }}"
>
    <x-slot name="icon">
        <!-- my icon -->
    </x-slot>
    Submit
</x-button>
```

To enable this feature you need to set the lexer on your twig enviroment. 
```php
$twig->setLexer(new Performing\TwigComponents\ComponentLexer($twig));
```

**Keep in mind** that you should set the lexer after you register all your twig extensions.

### Craft CMS
For example in Craft CMS you should do something like this. The `if` statement ensure you don't get `'Unable to register extension "..." as extensions have already been initialized'` as error.
```php
// Module.php

if (Craft::$app->request->getIsSiteRequest()) {    
    Event::on(
        Plugins::class,
        Plugins::EVENT_AFTER_LOAD_PLUGINS,
        function (Event $event) {
            $twig = Craft::$app->getView()->getTwig();
            \Performing\TwigComponents\Setup::init($twig, '/components');
        }
    );
}
```

### Twig Namespaces

In addition to the specified directory, you can also reference components from a twig namespace by prepending the namespace and a `:` to the component name. With a namespace defined like so:

```php
// register namespace with twig template loader
$loader->addPath(__DIR__ . '/some/other/dir', 'ns');
```

components can be included with the following:

```twig
{% x:ns:button with {class:'bg-blue-600'} %}
  <span class="text-lg">Click here!</span>
{% endx %}

{# or #}

<x-ns:button class='bg-blue-600'>
  <span class="text-lg">Click here!</span>
</x-ns:button>
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
