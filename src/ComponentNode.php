<?php

namespace Digital\TwigComponents;

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
            ->write('$slot = new \Digital\TwigComponents\SlotClosure(function (){')
            ->indent(3)
            ->subcompile($this->getNode('slot'))
            ->outdent(3)
            ->write("});\n\n")
            ->write(sprintf('$%s->display(', $template))
        ;

        $this->addTemplateArguments($compiler);

        $compiler
            ->raw(");\n")
            ->outdent()
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
        if (! $this->hasNode('variables')) {
            $compiler->raw('$context');
        } else {
            $compiler
                ->indent(5)
                ->write("\n")
                ->write("array_merge([\n")
                ->write(" 'slot' => \$slot,\n")
                ->write("'attributes' => new \Digital\TwigComponents\ComponentAttributes(")
                ->subcompile($this->getNode('variables'), true)
                ->raw(")")
                ->raw("],")
                ->subcompile($this->getNode('variables'), true)
                ->raw(")\n");
        }
    }
}
