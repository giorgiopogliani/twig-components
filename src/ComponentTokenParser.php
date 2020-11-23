<?php

namespace Performing\TwigComponents;

use Exception;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

final class ComponentTokenParser extends IncludeTokenParser
{
    /**
     * @var String Directory for the components files.
     */
    private $path;

    /**
     * ComponentTokenParser constructor.
     * @param string $tag
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getComponentPath(string $name)
    {
        return rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name . '.twig';
    }

    public function parse(Token $token): Node
    {
        list($variables, $name) = $this->parseArguments();

        $slot = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new ComponentNode($this->getComponentPath($name), $slot, $variables, $token->getLine());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $name = null;
        $variables = null;

        if ($stream->nextIf(Token::PUNCTUATION_TYPE, ':')) {
            $name = $this->parseComponentName();
        }

        if ($stream->nextIf(/* Token::NAME_TYPE */5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */3);

        return [$variables, $name];
    }

    public function parseComponentName(): string
    {
        $stream = $this->parser->getStream();

        $path = [];
        do {
            if ($this->parser->getCurrentToken()->getType() != /** Token::NAME_TYPE */ 5) {
                throw new Exception('First token must be a name type');
            }

            $name = $stream->next()->getValue();

            while ($stream->nextIf(Token::OPERATOR_TYPE, '-')) {
                $token = $stream->nextIf(Token::NAME_TYPE);
                if (! is_null($token)) {
                    $name .= '-' . $token->getValue();
                }
            }

            $path[] = $name;
        } while ($stream->nextIf(9 /** Token::PUNCTUATION_TYPE */, '.'));

        return implode('/', $path);
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endx');
    }

    public function getTag(): string
    {
        return 'x';
    }
}
