<?php

namespace Performing\TwigComponents;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;

final class ComponentNode extends IncludeNode
{
    public function __construct(string $path, Node $slot, ?AbstractExpression $variables, int $lineno)
    {
        parent::__construct(new ConstantExpression('not_used', $lineno), $variables, false, false, $lineno, null);

        $this->setAttribute('path', $path);
        $this->setNode('slot', $slot);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $template = $compiler->getVarName();

        $compiler->write(sprintf("$%s = ", $template));

        $this->addGetTemplate($compiler);

        $compiler->raw(";\n")
            ->write(sprintf("if ($%s) {\n", $template))
            ->write('$slot = new \Performing\TwigComponents\SlotClosure(function () use ($context, $macros) {')
            ->subcompile($this->getNode('slot'))
            ->write("});\n\n")
            ->write(sprintf('$%s->display(', $template))
        ;

        $this->addTemplateArguments($compiler);

        $compiler
            ->raw(");\n")
            ->write("}\n")
        ;
    }

    protected function addGetTemplate(Compiler $compiler)
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->repr($this->getTemplateName())
            ->raw(', ')
            ->repr($this->getTemplateName())
            ->raw(', ')
            ->repr($this->getTemplateLine())
            ->raw(')')
        ;
    }

    public function getTemplateName()
    {
        return $this->getAttribute('path');
    }

    protected function addTemplateArguments(Compiler $compiler)
    {
        $compiler
            ->indent(5)
            ->write("\n")
            ->write("array_merge([\n")
            ->write(" 'slot' => \$slot,\n")
            ->write("'attributes' => new \Performing\TwigComponents\ComponentAttributes(");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->raw(")")->raw("],");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->raw(")\n");
    }
}
