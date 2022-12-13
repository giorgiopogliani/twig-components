<?php

namespace Performing\TwigComponents\Tests;

trait ComponentsTestTrait
{
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
    public function test_class_merge_works_with_components_in_components()
    {
        $template = $this->twig->createTemplate(<<<HTML
        <x-button.red class="mb-5">Click me</x-button.red>
        HTML);
        $html = $template->render();

        $this->assertEquals('<button class="mb-5 bg-red-500 text-white">Click me</button>', $html);
    }

    /** @test */
    public function test_attributes_dont_conflict_with_components_in_components()
    {
        $template = $this->twig->createTemplate(<<<HTML
        <x-button.blue class="mb-5">Click me</x-button.blue>
        HTML);
        $html = $template->render();

        $this->assertEquals('<div class="mb-5 bg-red-500"><button class="text-white">Click me</button></div>', $html);
    }

    /** @test */
    public function test_class_components()
    {
        $template = $this->twig->createTemplate(<<<HTML
        <x-alert message="this is a message">Click me</x-alert>
        HTML);
        $html = $template->render();

        $this->assertEquals('<div>text-indigo-50 bg-indigo-400this is a message</div>', $html);
    }
}
