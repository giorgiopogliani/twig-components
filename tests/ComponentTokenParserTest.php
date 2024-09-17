<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\TokenParser\ComponentTokenParser;
use PHPUnit\Framework\TestCase;


class ComponentTokenParserTest extends TestCase
{
    private Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = $this->createMock(Configuration::class);
    }

    public function testGetComponentPathWithHintPath()
    {
        $this->configuration->method('getNeedsHintPath')
            ->willReturn(true);

        $this->configuration->method('getTemplatesPath')
            ->willReturn('mynamespace.myplugin::components');

        $this->configuration->method('getTemplatesExtension')
            ->willReturn('twig');

        $parser = new ComponentTokenParser($this->configuration);

        $componentPath = $parser->getComponentPath('test/component');

        $this->assertEquals('mynamespace.myplugin::components.test.component', $componentPath);
    }

    public function testGetComponentPathWithoutHintPath()
    {
        $this->configuration->method('getNeedsHintPath')
            ->willReturn(false);

        $this->configuration->method('getTemplatesPath')
            ->willReturn('mynamespace.myplugin::components');

        $this->configuration->method('getTemplatesExtension')
            ->willReturn('twig');

        $parser = new ComponentTokenParser($this->configuration);

        $componentPath = $parser->getComponentPath('test/component');

        $this->assertNotEquals('mynamespace.myplugin::components.test.component', $componentPath);
    }
}