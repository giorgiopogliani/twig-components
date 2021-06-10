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

        $compiler
            ->write('ob_start();')
            ->subcompile($this->getNode('body'))
            ->write('$body = ob_get_clean();' . PHP_EOL)
            ->write("\$slots['$name'] = new " . SlotBag::class . "(\$body);");
    }
}
