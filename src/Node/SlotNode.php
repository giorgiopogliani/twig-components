<?php

namespace Performing\TwigComponents\Node;

use Performing\TwigComponents\View\ComponentSlot;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

use Twig\Node\NodeOutputInterface;

final class SlotNode extends Node implements NodeOutputInterface
{
    public function __construct($name, $body, ?AbstractExpression $variables, int $lineno = 0)
    {
        parent::__construct(['body' => $body], ['name' => $name], $lineno, null);

        if ($variables) {
            $this->setNode('variables', $variables);
        }
    }

    public function compile(Compiler $compiler): void
    {
        $name = $this->getAttribute('name');

        $compiler
            ->write('ob_start();')
            ->subcompile($this->getNode('body'))
            ->write('$body = ob_get_clean();' . PHP_EOL)
            ->write("\$slots['$name'] = new " . ComponentSlot::class . "(\$body, ");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->write(");");
    }
}
