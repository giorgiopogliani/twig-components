<?php

namespace Performing\TwigComponents\View;

use Performing\TwigComponents\Configuration;

class AnonymousComponent extends Component
{
    private array $attributes;

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function template(): string
    {
        if (strpos($this->name, '@') === 0) {
            return $this->name . '.' . $this->configuration->getTemplatesExtension();
        }

        $componentPath = rtrim($this->configuration->getTemplatesPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->name;

        if ($this->configuration->isUsingTemplatesExtension()) {
            $componentPath .= '.' . $this->configuration->getTemplatesExtension();
        }

        return $componentPath;
    }
}
