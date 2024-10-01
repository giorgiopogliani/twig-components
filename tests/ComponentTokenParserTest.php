<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\TokenParser\ComponentTokenParser;
use PHPUnit\Framework\TestCase;

class ComponentTokenParserTest extends TestCase
{
    public function testGetComponentPathWithHintPath()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);

        $config = Configuration::make($twig)
            ->setTemplatesPath('mynamespace.myplugin::components', hint: true)
            ->setTemplatesExtension('twig')
            ->useCustomTags();

        $this->assertTrue($config->getNeedsHintPath());
        $this->assertEquals($config->getTemplatesPath(), 'mynamespace.myplugin::components');
        $this->assertEquals($config->getTemplatesExtension(), 'twig');

        $parser = new ComponentTokenParser($config);
        $componentPath = $parser->getComponentPath('test/component');
        $this->assertEquals('mynamespace.myplugin::components.test.component', $componentPath);
    }

    public function testGetComponentPathWithoutHintPath()
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);

        $config = Configuration::make($twig)
            ->setTemplatesPath('_components', hint: false)
            ->setTemplatesExtension('twig')
            ->useCustomTags();

        $this->assertFalse($config->getNeedsHintPath());
        $this->assertEquals($config->getTemplatesPath(), '_components');
        $this->assertEquals($config->getTemplatesExtension(), 'twig');

        $parser = new ComponentTokenParser($config);

        $componentPath = $parser->getComponentPath('test/component');

        $this->assertEquals('_components/test/component.twig', $componentPath);
    }
}
