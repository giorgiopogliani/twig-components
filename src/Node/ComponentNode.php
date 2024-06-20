<?php

namespace Performing\TwigComponents\Node;

use Exception;
use Performing\TwigComponents\Configuration;
use Performing\TwigComponents\View\ComponentAttributeBag;
use Performing\TwigComponents\View\ComponentSlot;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;

use Twig\Node\IncludeNode;
use Twig\Node\Node;

final class ComponentNode extends IncludeNode
{
    private Configuration $configuration;

    public function __construct(string $path, Node $slot, ?AbstractExpression $variables, int $lineno, Configuration $configuration)
    {
        parent::__construct(new ConstantExpression('not_used', $lineno), $variables, false, false, $lineno, null);

        $this->configuration = $configuration;
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
        $repr = $this->isDynamicComponent() ? 'raw' : 'repr';

        $compiler
            ->raw('$this->loadTemplate(' . PHP_EOL)
            ->indent(1)
            ->write('')
            ->$repr($this->getTemplateName())
            ->raw(', ' . PHP_EOL)
            ->write('')
            ->$repr($this->getTemplateName())
            ->raw(', ' . PHP_EOL)
            ->write('')
            ->repr($this->getTemplateLine())
            ->indent(-1)
            ->raw(PHP_EOL . ');' . PHP_EOL . PHP_EOL);
    }

    public function isDynamicComponent()
    {
        return strpos($this->getAttribute('path'), 'dynamic-component') !== false;
    }

    public function getDynamicComponent()
    {
        $component = null;

        foreach (array_chunk($this->getNode('variables')->nodes, 2) as $pair) {
            /** @var \Twig\Node\Expression\AbstractExpression $key */
            $key = $pair[0];
            /** @var \Twig\Node\Expression\AbstractExpression $value */
            $value = $pair[1];

            if ($key->getAttribute('value') !== 'component') {
                continue;
            }

            if ($value->hasAttribute('value')) {
                // Returns the component string value
                $component = '\'' . $value->getAttribute('value') . '\'';
                break;
            }

            if ($value->hasAttribute('name')) {
                // Uses the context to get the component value
                $component = '($context[\'' . $value->getAttribute('name') . '\'] ?? null)';
                break;
            }
        }

        if (!$component) {
            throw new Exception('Dynamic component must have a component attribute');
        }

        // TODO: Convert namespace to @ notation

        // Converts dot notation to directory separator
        $component = 'str_replace(\'.\', DIRECTORY_SEPARATOR, ' . $component . ')';

        $path = str_replace('dynamic-component', '\' . ' . $component . ' . \'', $this->getAttribute('path'));

        return "'$path'";
    }

    public function getTemplateName(): ?string
    {
        if ($this->isDynamicComponent()) {
            return $this->getDynamicComponent();
        }

        return $this->getAttribute('path');
    }

    protected function addTemplateArguments(Compiler $compiler)
    {
        $this->filterVariables();

        $compiler
            ->indent(1)
            ->write("\n")
            ->write("array_merge(\n")
            ->write('$slots,' . PHP_EOL);

        if ($this->configuration->isUsingGlobalContext()) {
            $compiler->write('$context,[');
        } else {
            $compiler->write('[');
        }

        $compiler->write("'slot' => new  " . ComponentSlot::class . " (\$slot),\n")
            ->write("'attributes' => new " . ComponentAttributeBag::class . "(");

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

    public function filterVariables()
    {
        if (!$this->isDynamicComponent()) {
            return;
        }

        $variables = $this->getNode('variables');

        foreach (array_chunk($variables->nodes, 2, true) as $pair) {
            /** @var \Twig\Node\Expression\AbstractExpression $key */
            $key = array_values($pair)[0];

            if ($key->getAttribute('value') === 'component') {
                $variables->removeNode(array_keys($pair)[0]);
                $variables->removeNode(array_keys($pair)[1]);
            }
        }
    }
}
