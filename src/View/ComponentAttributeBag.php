<?php

namespace Performing\TwigComponents\View;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class ComponentAttributeBag implements ArrayAccess, IteratorAggregate
{
    /**
     * The raw array of attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new component attribute bag instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;

        if (array_key_exists('attributes', $this->attributes) && $this->attributes['attributes'] instanceof ComponentAttributeBag) {
            $parentAttributes = $this->attributes['attributes'];
            unset($this->attributes['attributes']);
            $this->attributes = $this->merge($parentAttributes->getAttributes())->getAttributes();
        }
    }

    /**
     * Get the first attribute's value.
     *
     * @param mixed $default
     * @return mixed
     */
    public function first($default = null)
    {
        return $this->getIterator()->current() ?? $default;
    }

    /**
     * Get a given attribute from the attribute array.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = '')
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Get a given attribute from the attribute array.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Only include the given attribute from the attribute array.
     *
     * @param mixed|array $keys
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = is_array($keys) ? $keys : [$keys];

            $values = array_filter(
                $this->attributes,
                function ($key) use ($keys) {
                    return in_array($key, $keys);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return new static($values);
    }

    /**
     * Exclude the attributes given from the attribute array.
     *
     * @param mixed|array keys
     * @return static
     */
    public function except($keys)
    {
        if (is_null($keys)) {
            $values = $this->attributes;
        } else {
            $keys = is_array($keys) ? $keys : [$keys];

            $values = array_filter(
                $this->attributes,
                function ($key) use ($keys) {
                    return ! in_array($key, $keys);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        return new static($values);
    }

    /**
     * Merge additional attributes / values into the attribute bag.
     *
     * @param array $attributeDefaults
     * @return static
     */
    public function merge(array $attributeDefaults = [])
    {
        $attributes = $this->getAttributes();

        foreach ($attributeDefaults as $key => $value) {
            if (! array_key_exists($key, $attributes)) {
                $attributes[$key] = '';
            }
        }

        foreach ($attributes as $key => $value) {
            $attributes[$key] = trim($value . ' ' . ($attributeDefaults[$key] ?? ''));
        }

        return new static($attributes);
    }

    public function class($defaultClass = '')
    {
        return $this->merge(['class' => $defaultClass]);
    }

    /**
     * Get all of the raw attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set the underlying attributes.
     *
     * @param array $attributes
     * @return void
     */
    public function setAttributes(array $attributes)
    {
        if (isset($attributes['attributes']) &&
            $attributes['attributes'] instanceof self) {
            $parentBag = $attributes['attributes'];

            unset($attributes['attributes']);

            $attributes = $parentBag->merge($attributes, $escape = false)->getAttributes();
        }

        $this->attributes = $attributes;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Remove the value at the given offset.
     *
     * @param string $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->attributes);
    }

    /**
     * Implode the attributes into a single HTML ready string.
     *
     * @return string
     */
    public function __toString()
    {
        $string = '';

        foreach ($this->attributes as $key => $value) {
            if ($value === false || is_null($value)) {
                continue;
            }

            if ($value === true) {
                $value = $key;
            }

            $string .= ' ' . $key . '="' . str_replace('"', '\\"', trim($value)) . '"';
        }

        return trim($string);
    }
}
