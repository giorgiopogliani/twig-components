<?php

namespace Performing\TwigComponents\View;

use Performing\TwigComponents\Configuration;
use ReflectionClass;
use ReflectionParameter;

abstract class Component
{
    protected static array $constructorParametersCache = [];

    protected ?string $name = null;

    protected ?Configuration $configuration = null;

    public function withName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function withConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Extract the constructor parameters for the component.
     *
     * @return array
     */
    protected static function extractConstructorParameters()
    {
        if (! isset(static::$constructorParametersCache[static::class])) {
            $class = new ReflectionClass(static::class);

            $constructor = $class->getConstructor();

            static::$constructorParametersCache[static::class] = $constructor
                ? array_merge(...array_map(fn (ReflectionParameter $param) => [$param->getName() => $param->getDefaultValue()], $constructor->getParameters()))
                : [];
        }

        return static::$constructorParametersCache[static::class];
    }

    /**
     * Make the component instance with the given data.
     *
     * @param  array  $data
     * @return static
     */
    public static function make($data = [])
    {
        $parameters = static::extractConstructorParameters();

        if (static::class === AnonymousComponent::class) {
            return new static($data);
        }

        return new static(...array_intersect_key(array_merge($parameters, $data), $parameters));
    }

    public function getContext($slots, $slot, $globalContext, $variables)
    {
        $context = [];

        $context = array_merge($context, $globalContext);
        $context = array_merge($context, $slots);
        $context = array_merge($context, $variables);

        $context['slot'] = new ComponentSlot($slot);
        $context['this'] = $this;

        foreach ((new ReflectionClass($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $context[$property->getName()] = &$this->{$property->getName()};
        }

        $context['attributes'] = new ComponentAttributeBag($variables);

        return $context;
    }

    abstract public function template(): string;
}
