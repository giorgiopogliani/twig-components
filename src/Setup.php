<?php

namespace Performing\TwigComponents;

use Twig\Environment;

class Setup
{
    /** @deprecated */
    public static function init(Environment $twig, $relativePath)
    {
        Configuration::make($twig)
            ->setTemplatesPath($relativePath)
            ->setTemplatesExtension('twig')
            ->useCustomTags()
            ->setup();

        return $twig;
    }
}
