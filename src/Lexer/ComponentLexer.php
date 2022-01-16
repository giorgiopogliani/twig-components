<?php

namespace Performing\TwigComponents\Lexer;

use Twig\Lexer;
use Twig\Source;
use Twig\TokenStream;

class ComponentLexer extends Lexer
{
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
        return (new ComponentTagCompiler($value))->compile();
    }
}
