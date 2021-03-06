<?php

namespace Performing\TwigComponents;

use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\NodeOutputInterface;

final class SlotNode extends Node implements NodeOutputInterface
{
    public function __construct($name, $body, int $lineno = 0)
    {
        parent::__construct(['body' => $body], ['name' => $name], $lineno, null);
    }

    public function compile(Compiler $compiler): void
    {
        $name = $this->getAttribute('name');

        $compiler->raw(";\n")
            ->write('$context["slots"] = array_merge($context["slots"], [ "' . $name . '" => new \Performing\TwigComponents\SlotClosure(function () use ($context, $macros, $blocks) {')
            ->subcompile($this->getNode('body'))
            ->write("})]);\n\n");
    }
}
