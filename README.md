# Twig components extension

[![Latest Version on Packagist](https://img.shields.io/packagist/v/performing/twig-components.svg?style=flat-square)](https://packagist.org/packages/performing/twig-components)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/giorgiopogliani/twig-components/Tests)](https://github.com/giorgiopogliani/twig-components/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/performing/twig-components.svg?style=flat-square)](https://packagist.org/packages/performing/twig-components)

This is a twig extension for automatically create components as tags. The name of the tag is based on files in a directory. This is highly inspired from blade components.  

## Installation

You can install the package via composer:

```bash
composer require performing/twig-components
```

You can create the twig extension that will find all the files in the given directory.
```php
$extension = new \Performing\TwigComponents\ComponentExtension('/relative/twig/components/directory');
```

For example, Craft CMS users can do the following:
```php
Craft::$app->view->registerTwigExtension(
    new \Performing\TwigComponents\ComponentExtension('/components')
);
```

## Syntax
```twig
{% x:component-name with {any: 'param'} %}
    <strong>Any Content</strong>
{% endx %}
```

You can also reach for components files that are in subfolders with a dot-notation syntax. For example, a component at `/components/button/primary.twig` would become
```twig
{% x:button.primary with {any: 'param'} %}
    <strong>Any Content</strong>
{% endx %}
```

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

For example in Craft CMS you should do somethind like this: 
```php
if (Craft::$app->request->getIsSiteRequest()) {
    Craft::$app->view->registerTwigExtension(
        new \Performing\TwigComponents\ComponentExtension('/components')
    );

    // Enable x-tags syntax
    $twig = Craft::$app->getView()->getTwig();
    $twig->setLexer(new \Performing\TwigComponents\ComponentLexer($twig));
}
```

### Components
You can create a file in the components directory like this.
```twig
{# /components/button.twig #}
<button {{ attributes.merge({ class:'text-white rounded-md px-4 py-2' })|raw }}>
  {{ slot }}
</button>
```

Use the new created tag.
```twig
{# /index.twig #}
{% x:button with {class:'bg-blue-600'} %}
  <span class="text-lg">Click here!</span>
{% endx %}
```

The output generated will be like this.
```html
<button class="bg-blue-600 text-white rounded-md px-4 py-2">
  <span class="text-lg">Click here!</span>
</button>
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
