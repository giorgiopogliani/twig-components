<?php

namespace Performing\TwigComponents\Extension;

use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\TokenParser\ComponentTokenParser;

use Performing\TwigComponents\TokenParser\SlotTokenParser;
use Twig\Extension\AbstractExtension;

class ComponentExtension extends AbstractExtension
{

    private Configuration $options;

    public function __construct(Configuration $options)
    {
        $this->options = $options;
    }

    public function getTokenParsers()
    {
        return [
            new ComponentTokenParser($this->options),
            new SlotTokenParser(),
        ];
    }
}
