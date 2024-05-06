<?php

namespace Performing\TwigComponents\Node;

use Performing\TwigComponents\View\ComponentSlot;
use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

use Twig\Node\NodeOutputInterface;

#[YieldReady]
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
            ->write('$body = (function () use (&$slots, &$context) {')
            ->subcompile($this->getNode('body'))
            ->write('})() ?? new \EmptyIterator();' . PHP_EOL)
            ->write('$body = implode("", iterator_to_array($body));' . PHP_EOL)
            ->write("\$slots['$name'] = new " . ComponentSlot::class . "(\$body, ");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->write(");");
    }
}
