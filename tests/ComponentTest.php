<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\ComponentExtension;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    /** @test */
    public function render_simple_button_component()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);

        $twig->addExtension(
            new ComponentExtension(__DIR__ . '/templates/components', '/components')
        );

        $html = $twig->render('index-button.twig');

        $this->assertEquals("<button class=\"bg-blue-600 text-white\"> test </button>\n", $html);
    }
}
