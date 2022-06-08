<?php

namespace Performing\TwigComponents;

use Performing\TwigComponents\Extension\ComponentExtension;
use Performing\TwigComponents\Lexer\ComponentLexer;
use Performing\TwigComponents\View\ComponentAttributeBag;
use Performing\TwigComponents\View\ComponentSlot;
use Twig\Environment;

class Configuration
{
    protected Environment $twig;

    protected bool $isUsingCustomTags = false;

    protected bool $isUsingGlobalContext = false;

    protected string $templatesPath = 'components';

    protected bool $isUsingTemplatesExtension = true;

    protected string $templatesExtension = 'twig';

    protected ?string $componentsNamespace = null;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public static function make(Environment $twig): Configuration
    {
        return new static($twig);
    }

    /**
     * Set relative path to components templates.
     *
     * @param string $path
     * @return Configuration
     */
    public function setTemplatesPath(string $path): self
    {
        $this->templatesPath = rtrim($path, DIRECTORY_SEPARATOR);

        return $this;
    }

    public function getTemplatesPath(): string
    {
        return $this->templatesPath;
    }

    public function useTemplatesExtension(bool $isUsing = true): self
    {
        $this->isUsingTemplatesExtension = $isUsing;

        return $this;
    }

    public function isUsingTemplatesExtension(): bool
    {
        return $this->isUsingTemplatesExtension;
    }

    /**
     * Set templates file extension. (default: twig)
     *
     * @param string $extension
     * @return Configuration
     */
    public function setTemplatesExtension(string $extension): self
    {
        $this->templatesExtension = ltrim($extension, '.');

        return $this;
    }

    public function getTemplatesExtension(): string
    {
        return $this->templatesExtension;
    }

    /**
     * Set namespace to autoload class components.
     *
     * @param string $namespace
     * @return Configuration
     */
    public function setComponentsNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getComponentsNamespace(): ?string
    {
        return $this->componentsNamespace;
    }

    /**
     * Enable global context inside components. Any global
     * variable will be availble to all components.
     *
     * @return Configuration
     */
    public function useGlobalContext(): self
    {
        $this->isUsingGlobalContext = true;

        return $this;
    }

    public function isUsingGlobalContext(): bool
    {
        return $this->isUsingGlobalContext;
    }

    /**
     * Enable custom tags. This will set the lexer and enable
     * the use of <x-tags> instead of the twig syntax.
     *
     * @return Configuration
     */
    public function useCustomTags(): self
    {
        $this->isUsingCustomTags = true;

        return $this;
    }

    public function isUsingCustomTags(): bool
    {
        return $this->isUsingCustomTags;
    }

    /**
     * Setup the twig environment.
     *
     * @return void
     */
    public function setup()
    {
        $this->twig->addExtension(new ComponentExtension($this));

        if ($this->isUsingCustomTags()) {
            $this->twig->setLexer(new ComponentLexer($this->twig));
        }

        /** @var \Twig\Extension\EscaperExtension */
        $escaper = $this->twig->getExtension(\Twig\Extension\EscaperExtension::class);
        $escaper->addSafeClass(ComponentAttributeBag::class, ['all']);
        $escaper->addSafeClass(ComponentSlot::class, ['all']);
    }
}
