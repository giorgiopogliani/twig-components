<?php

namespace Performing\TwigComponents\Extension;

use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\TokenParser\ComponentTokenParser;

use Performing\TwigComponents\TokenParser\SlotTokenParser;
use Twig\Extension\AbstractExtension;

class ComponentExtension extends AbstractExtension
{
    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getTokenParsers()
    {
        return [
            new ComponentTokenParser($this->configuration),
            new SlotTokenParser(),
        ];
    }
}
