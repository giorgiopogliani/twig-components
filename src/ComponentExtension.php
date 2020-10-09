<?php


namespace Performing\TwigComponents;

use Twig\Extension\AbstractExtension;

class ComponentExtension extends AbstractExtension
{
    private string $path;

    private string $relativePath;

    public function __construct(string $path, string $relativePath)
    {
        $this->path = rtrim($path, DIRECTORY_SEPARATOR);

        $this->relativePath = rtrim($relativePath, DIRECTORY_SEPARATOR);
    }

    public function getTokenParsers()
    {
        $files = glob($this->path. DIRECTORY_SEPARATOR . "*.twig");

        return array_map(function ($file) {
            return new ComponentTokenParser(basename($file), $this->relativePath);
        }, $files);
    }
}
