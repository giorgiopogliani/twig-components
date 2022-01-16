<?php

namespace Performing\TwigComponents\Extension;

use Twig\Extension\AbstractExtension;

use Performing\TwigComponents\TokenParser\ComponentTokenParser;
use Performing\TwigComponents\TokenParser\SlotTokenParser;

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
