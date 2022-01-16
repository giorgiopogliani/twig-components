<?php

namespace Performing\TwigComponents\Extension;

use Performing\TwigComponents\TokenParser\ComponentTokenParser;

use Performing\TwigComponents\TokenParser\SlotTokenParser;
use Twig\Extension\AbstractExtension;

class ComponentExtension extends AbstractExtension
{
    /**
     * @var string
     */
    private $relativePath;

    public function __construct(string $relativePath)
    {
        $this->relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
    }

    public function getTokenParsers()
    {
        return [
            new ComponentTokenParser($this->relativePath),
            new SlotTokenParser(),
        ];
    }
}
