<?php

namespace Performing\TwigComponents;

use Twig\Extension\AbstractExtension;

class ComponentExtension extends AbstractExtension
{
    /**
     * @var []
     */
    private $relativePaths = [];

    public function __construct($relativePaths)
    {
        if (\is_string($relativePaths)) {
            $this->relativePaths[] = rtrim($relativePaths, DIRECTORY_SEPARATOR);
        } else {
            foreach ($relativePaths as $relativePath) {
                $this->relativePaths[] = rtrim($relativePath, DIRECTORY_SEPARATOR);
            }
        }
    }

    public function getTokenParsers()
    {
        $parsers = [
            new SlotTokenParser(),
        ];

        foreach ($this->relativePaths as $relativePath) {
            $parsers[] = new ComponentTokenParser($relativePath);
        }

        return $parsers;
    }
}
