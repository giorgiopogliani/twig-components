<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\Configuration;
use PHPUnit\Framework\TestCase;

class ComponentTest extends TestCase
{
    protected $twig;

    protected function setupTwig(): \Twig\Environment
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        $loader->addPath(__DIR__ . '/namespace-templates', 'ns');

        $twig = new \Twig\Environment($loader, [
            'cache' => false,//__DIR__ . '/../cache',
        ]);

        Configuration::make($twig)
            ->setTemplatesPath('components')
            ->setTemplatesExtension('twig')
            ->useGlobalContext(false)
            ->setup();

        return $twig;
    }

    public function setUp(): void
    {
        $this->twig = $this->setupTwig();
    }

    /** @test */
    public function render_simple_component()
    {
        $html = $this->twig->render('test_simple_component.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-600 text-white"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_simple_component_with_dash()
    {
        $html = $this->twig->render('test_simple_component_with_dash.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-700 text-white"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_simple_component_in_folder()
    {
        $html = $this->twig->render('test_simple_component_in_folder.twig');

        $this->assertEquals(<<<HTML
        <button class="text-white bg-blue-800 rounded"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_component_with_slots()
    {
        $html = $this->twig->render('test_with_slots.twig');

        $this->assertEquals(<<<HTML
        <div><span>test</span><div>test</div></div>
        HTML, $html);
    }

    /** @test */
    public function render_xtags_with_slots()
    {
        $html = $this->twig->render('test_xtags_with_slots.twig');

        $this->assertEquals(<<<HTML
        <div><span>test</span><div>test</div></div>
        HTML, $html);
    }

    /** @test */
    public function render_nested_xtags_with_slots()
    {
        $html = $this->twig->render('test_nested_xtags_with_slots.twig');

        $this->assertEquals(<<<HTML
        <div><span>[outer name]</span><div>[inner name][inner slot]</div></div>
        HTML, $html);
    }

    /** @test */
    public function render_deeply_nested_xtags_with_slots()
    {
        $html = $this->twig->render('test_deeply_nested_xtags_with_slots.twig');
        $html = preg_replace('/\s{2,}/', '', $html); // ignore whitespace difference

        $this->assertEquals(<<<HTML
        <div><span>A</span><div>BC<div>D<button class="text-white">E</button><div>FG</div></div></div></div>
        HTML, $html);
    }

    /** @test */
    public function render_component_with_xtags()
    {
        $html = $this->twig->render('test_xtags_component.twig');

        $this->assertEquals(<<<HTML
        <button class="text-white bg-blue-800 rounded"> test1 </button>
        <button class="text-white bg-blue-800 rounded"> test2 </button>
        <button class="'text-white' bg-blue-800 rounded"> test3 </button>
        HTML, $html);
    }

    /** @test */
    public function render_component_with_attributes()
    {
        $html = $this->twig->render('test_with_attributes.twig');

        $this->assertEquals(<<<HTML
        <div>
         1
         {"foo":1}
         bar
        </div>
        HTML, $html);
    }

    /** @test */
    public function render_namespaced_component()
    {
        $html = $this->twig->render('test_namespaced_component.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-600 ns-button text-white"> test </button>
        HTML, $html);
    }

    /** @test */
    public function render_namespaced_xtags_component()
    {
        $html = $this->twig->render('test_namespaced_xtags_component.twig');

        $this->assertEquals(<<<HTML
        <button class="bg-blue-600 ns-button text-white"> test1 </button>
        <button class="bg-blue-600 ns-button text-white"> test2 </button>
        <button class="'bg-blue-600' ns-button text-white"> test3 </button>
        HTML, $html);
    }

    /** @test */
    public function share_global_context_inside_components()
    {
        $template = $this->twig->createTemplate(<<<HTML
        {% set foo = 'bar' %}
        <x-global_context></x-global_context>
        HTML);
        $html = $template->render();

        $this->assertEquals('bar', $html);
    }
}
