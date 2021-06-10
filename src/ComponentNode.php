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

        $compiler
            ->write(sprintf("if ($%s) {\n", $template))
            ->write('$slots = [];' . PHP_EOL)
            ->write("ob_start();"  . PHP_EOL)
            ->subcompile($this->getNode('slot'))
            ->write('$slot = ob_get_clean();' . PHP_EOL)
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
            ->raw('$this->loadTemplate(' . PHP_EOL)
            ->indent(1)
            ->write('')
            ->repr($this->getTemplateName())
            ->raw(', ' . PHP_EOL)
            ->write('')
            ->repr($this->getTemplateName())
            ->raw(', ' . PHP_EOL)
            ->write('')
            ->repr($this->getTemplateLine())
            ->indent(-1)
            ->raw(PHP_EOL . ');' . PHP_EOL . PHP_EOL)
        ;
    }

    public function getTemplateName(): ?string
    {
        return $this->getAttribute('path');
    }

    protected function addTemplateArguments(Compiler $compiler)
    {
        $compiler
        ->indent(1)
        ->write("\n")
        ->write("array_merge(\n")
        ->write('$slots,[' . PHP_EOL)
        ->write("'slot' => new  " . SlotBag::class . " (\$slot),\n")
        ->write("'attributes' => new " . AttributesBag::class . "(");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->write(")\n")
                ->indent(-1)
                ->write("],");

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->write(")\n");
    }
}
