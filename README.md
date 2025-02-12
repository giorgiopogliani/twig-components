# Twig components extension

[![Packagist Version](https://img.shields.io/packagist/v/performing/twig-components)](https://packagist.org/packages/performing/twig-components) [![Tests](https://github.com/giorgiopogliani/twig-components/actions/workflows/run-tests.yml/badge.svg)](https://github.com/giorgiopogliani/twig-components/actions/workflows/run-tests.yml) [![Packagist Downloads](https://img.shields.io/packagist/dt/performing/twig-components)](https://packagist.org/packages/performing/twig-components)

This is a PHP package for automatically create Twig components as tags. This is highly inspired from Laravel Blade Components.

## Installation

You can install the package via Composer:

```bash
composer require performing/twig-components
```

## Configuration

This package should work anywhere where Twig is available.

```php
use Performing\TwigComponents\Configuration;

/** @var \Twig\Environment $twig */
Configuration::make($twig)
    ->setTemplatesPath('/relative/directory/to/components') // default is 'components'
    ->setTemplatesExtension('twig') // default is 'twig'
    ->setup();
```

To enable the package just pass your Twig environment object to the function and specify your components folder relative to your Twig templates folder.

### Configuration for Craft CMS

In Craft CMS you should do something like this:

```php
// Module.php
if (Craft::$app->request->getIsSiteRequest()) {    
    Event::on(
        Plugins::class,
        Plugins::EVENT_AFTER_LOAD_PLUGINS,
        function (Event $event) {
            $twig = Craft::$app->getView()->getTwig();
            \Performing\TwigComponents\Configuration::make($twig)->setup();
        }
    );
}
```

> The `if` statement ensure you don't get `'Unable to register extension "..." as extensions have already been initialized'` as error.

### Configuration for Symfony

In Symfony you can do something like this:

```yml
# services.yml
services:
    My\Namespace\TwigEnvironmentConfigurator:
        decorates: 'twig.configurator.environment'
        arguments: ['@My\Namespace\TwigEnvironmentConfigurator.inner']
```

```php
// TwigEnvironmentConfigurator.php
use Symfony\Bundle\TwigBundle\DependencyInjection\Configurator\EnvironmentConfigurator;
use Twig\Environment;
use Performing\TwigComponents\Configuration;

final class TwigEnvironmentConfigurator
{
    public function __construct(
        private EnvironmentConfigurator $decorated
    ) {}

    public function configure(Environment $environment) : void
    {
        $this->decorated->configure($environment);
        Configuration::make($environment)
            ->setTemplatesPath('/relative/directory/to/components')
            ->setup();
    }
}
```

### Configuration for October CMS / Winter CMS

In October CMS and Winter CMS you need to hook into `cms.page.beforedisplay` event inside your plugin's boot method in order to access Twig instance.  
Then you can use your plugin hint path to choose a `views` subfolder as components' folder.

```text
plugins
|__namespace
   |__pluginname
      |__views
         |__components
            |__button.twig
```

```php
public function boot(): void
{
    Event::Listen('cms.page.beforeDisplay', function ($controller, $url, $page) {
        $twig = $controller->getTwig();
        Configuration::make($twig)
            ->setTemplatesPath('namespace.pluginname::components', hint: true)
            ->useGlobalContext() // use this to keep Twig context from CMS
            ->setup();
    });
}
```

> All features, like subfolders are supported. For example `<x-forms.input></x-forms.input>` will refer to `plugins/namespace/pluginname/views/forms/input.twig`.

## Usage

Components are just Twig templates in a folder of your choice (e.g. `/components`) and can be used anywhere in your Twig templates:

```twig
{# /components/button.twig #}
<button>
    {{ slot }}
</button>
```

> The slot variable is any content you will add between the opening and the close tag.

To reach a component you need to use the dedicated tag `x` followed by `:` and the filename of your component without extension:

```twig
{# /index.twig #}
{% x:button %}
    <strong>Click me</strong>
{% endx %}
```

It will render:

```twig
<button>
    <strong>Click me</strong>
</button>
```

### Custom tags

The same behaviour can be obtained with a **special HTML syntax**.

```php
Configuration::make($twig)
    ->setTemplatesPath('/relative/directory/to/components')
    ->useCustomTags()
    ->setup();
```

```twig
{# /index.twig #}
<x-button class="text-white">
    <strong>Click me</strong>
</x-button>
```

### Attributes

You can also pass any params like you would using an `include`. The benefit is that you will have the powerful `attributes` variable to merge attributes or to change your component behaviour.

```twig
{# /components/button.twig #}
<button {{ attributes.merge({class: 'rounded px-4'}) }}>
    {{ slot }}
</button>

{# /index.twig #}
{% x:button with {class: 'text-white'} %}
    <strong>Click me</strong>
{% endx %}

{# Rendered #}
<button class="text-white rounded px-4">
    <strong>Click me</strong>
</button>
```

With **custom tags** you can pass any attribute to the component in different ways. To interprate the content as Twig you need to prepend the attribute name with a `:`, but it works also in other ways.

```twig
<x-button 
    :any="'evaluate' ~ 'twig'"
    other="{{'this' ~ 'works' ~ 'too'}}" 
    another="or this"
    this="{{'this' ~ 'does'}}{{'not work'}}"
>
    Submit
</x-button>
```

### Subfolders

To reach components in subfolders you can use _dot-notation_ syntax.

```twig
{# /components/button/primary.twig #}
<button>
    {{ slot }}
</button>

{# /index.twig #}
{% x:button.primary %}
    <strong>Click me</strong>
{% endx %}
```

### Named slots

In case of use of multiple slots you can name them.

```twig
{# /components/card.twig #}
<div {{ attributes.class('bg-white shadow p-3 rounded') }}>
    <h2 {{ title.attributes.class('font-bold') }}>
        {{ title }}
    </h2>
    <div>
        {{ body }}
    </div>
</div>
```

Use with standard syntax:

```twig
{# /index.twig #}
{% x:card %}
    {% slot:title with {class: 'text-2xl'} %}Title{% endslot %}
    {% slot:body %}Body{% endslot %}
{% endx %}
```

Use with custom tags syntax:

```twig
{# /index.twig #}
<x-card>
    <x-slot name="title" class="text-2xl">Title</x-slot>
    <x-slot name="body">Body</x-slot>
</x-card>
```

### Twig namespaces

In addition to the specified directory, you can also reference components from a Twig namespace by prepending the component name with `<namespace>:`.

```php
// register namespace with Twig templates loader
$loader->addPath(__DIR__ . '/some/other/dir', 'ns');
```

Use with standard syntax:

```twig
{% x:ns:button with {class: 'bg-blue-600'} %}
    <strong>Click me</strong>
{% endx %}
```

Use with custom tags syntax:

```twig
<x-ns:button class="bg-blue-600">
    <strong>Click me</strong>
</x-ns:button>
```

### Dynamic components

Sometimes you may need to render a component but not know which component should be rendered until runtime.  
In this situation, you may use the built-in `dynamic-component` to render the component based on a runtime value or variable:

```twig
{% set componentName = 'button' %}
<x-dynamic-component :component="componentName" class="bg-blue-600">
    <strong>Click me</strong>
</x-dynamic-component>
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
