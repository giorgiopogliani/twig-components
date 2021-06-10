<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\Setup;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    protected function setupTwig(): \Twig\Environment
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        $twig = new \Twig\Environment($loader, [
            'cache' => false,
        ]);

        Setup::init($twig, '/components');

        return $twig;
    }

    /** @test */
    public function render_simple_component()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_simple_component.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-600 text-white"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_simple_component_with_dash()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_simple_component_with_dash.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-700 text-white"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_simple_component_in_folder()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_simple_component_in_folder.twig');

        $this->assertEquals(<<<HTML
        <button class="text-white bg-blue-800 rounded"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_component_with_slots()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_with_slots.twig');

        $this->assertEquals(<<<HTML
        <div><span>test</span><div>test</div></div>
        HTML, $html);
    }

    /** @test */
    public function render_xtags_with_slots()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_xtags_with_slots.twig');

        $this->assertEquals(<<<HTML
        <div><span>test</span><div>test</div></div>
        HTML, $html);
    }

    /** @test */
    public function render_component_with_xtags()
    {
        $twig = $this->setupTwig();

        $html = $twig->render('test_xtags_component.twig');

        $this->assertEquals(<<<HTML
        <button class="text-white bg-blue-800 rounded"> test1 </button>
        <button class="text-white bg-blue-800 rounded"> test2 </button>
        <button class="'text-white' bg-blue-800 rounded"> test3 </button>
        HTML, $html);
    }
}
