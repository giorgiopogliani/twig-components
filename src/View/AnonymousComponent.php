<?php

namespace Performing\TwigComponents\View;

use Performing\TwigComponents\Configuration;

class AnonymousComponent extends Component
{
    private string $name;

    private Configuration $configuration;

    public function __construct(string $name, Configuration $configuration)
    {
        $this->name = $name;
        $this->configuration = $configuration;
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
