<?php

namespace Performing\TwigComponents\Node;

use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\View\Component;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\IncludeNode;
use Twig\Node\Node;

final class ComponentNode extends IncludeNode
{
    private Configuration $configuration;

    private Component $component;

    public function __construct(Component $component, Node $slot, ?AbstractExpression $variables, int $lineno, Configuration $configuration)
    {
        parent::__construct(new ConstantExpression('not_used', $lineno), $variables, false, false, $lineno, null);

        $this->configuration = $configuration;
        $this->component = $component;
        $this->setAttribute('component', get_class($component));
        $this->setAttribute('path', $component->template());
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
            ->write('$slotsStack = $slotsStack ?? [];' . PHP_EOL)
            ->write('$slotsStack[] = $slots ?? [];' . PHP_EOL)
            ->write('$slots = [];' . PHP_EOL)
            ->write("ob_start();"  . PHP_EOL)
            ->subcompile($this->getNode('slot'))
            ->write('$slot = ob_get_clean();' . PHP_EOL)
            ->write(sprintf('$%s->display(', $template));

        $this->addTemplateArguments($compiler);

        $compiler
            ->raw(");\n")
            ->write('$slots = array_pop($slotsStack);' . PHP_EOL)
            ->write("}\n");
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
            ->raw(PHP_EOL . ');' . PHP_EOL . PHP_EOL);
    }

    public function getTemplateName(): ?string
    {
        return $this->getAttribute('path');
    }

    protected function addTemplateArguments(Compiler $compiler)
    {
        $compiler->write($this->getAttribute('component') . "::make(" . PHP_EOL);
        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }
        $compiler->write(PHP_EOL . ')->getContext($slots, $slot,');

        if ($this->configuration->isUsingGlobalContext()) {
            $compiler->write('$context,');
        } else {
            $compiler->write('[],');
        }

        if ($this->hasNode('variables')) {
            $compiler->subcompile($this->getNode('variables'), true);
        } else {
            $compiler->raw('[]');
        }

        $compiler->write(')');
    }
}
