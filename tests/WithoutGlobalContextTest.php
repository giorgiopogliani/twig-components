<?php

namespace Performing\TwigComponents\Tests;

use Performing\TwigComponents\Configuration;
use PHPUnit\Framework\TestCase;

class WithoutGlobalContextTest extends TestCase
{
    use ComponentsTestTrait;

    protected $twig;

    protected function setupTwig(): \Twig\Environment
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');

        $loader->addPath(__DIR__ . '/namespace-templates', 'ns');

        $twig = new \Twig\Environment($loader, [
            'cache' => false, //__DIR__ . '/../cache',
        ]);

        Configuration::make($twig)
            ->setTemplatesPath('components')
            ->setTemplatesExtension('twig')
            ->setComponentsNamespace('\Performing\TwigComponents\Tests\View')
            ->register('test', \Performing\TwigComponents\Tests\View\Alert::class)
            ->useCustomTags()
            ->setup();

        return $twig;
    }

    public function setUp(): void
    {
        $this->twig = $this->setupTwig();
    }
}
