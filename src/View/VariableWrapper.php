<?php

namespace Performing\TwigComponents\View;

class VariableWrapper
{
    public function __construct(
        protected Component $component,
        protected string $name
    ) {
    }

    public function __toString()
    {
        return $this->component->{$this->name} ?? '';
    }
}
