<?php

namespace Performing\TwigComponents;

use Performing\TwigComponents\Extension\ComponentExtension;

use Performing\TwigComponents\Lexer\ComponentLexer;
use Performing\TwigComponents\View\ComponentAttributeBag;
use Performing\TwigComponents\View\ComponentSlot;
use Twig\Environment;

class Setup
{
    public static function init(Environment $twig, $relativePath)
    {
        $twig->addExtension(new ComponentExtension($relativePath));

        $twig->setLexer(new ComponentLexer($twig));

        /** @var \Twig\Extension\EscaperExtension */
        $escaper = $twig->getExtension(\Twig\Extension\EscaperExtension::class);
        $escaper->addSafeClass(ComponentAttributeBag::class, ['all']);
        $escaper->addSafeClass(ComponentSlot::class, ['all']);

        return $twig;
    }
}
