<?php

namespace Performing\TwigComponents;

use Twig\Lexer;
use Twig\Source;
use Twig\TokenStream;

class ComponentLexer extends Lexer
{
    protected $config;

    public function tokenize(Source $source): TokenStream
    {
        $preparsed = $this->preparse($source->getCode());

        return parent::tokenize(
            new Source(
                $preparsed,
                $source->getName(),
                $source->getPath()
            )
        );
    }

    protected function preparse(string $value): string
    {
        return (new ComponentTagCompiler($value))
            ->withConfig($this->config)
            ->compile();
    }

    public function withConfig($config = null)
    {
        if (! \is_null($config)) {
            $this->config = $config;
        }

        return $this;
    }
}
