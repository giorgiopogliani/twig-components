<?php

namespace Performing\TwigComponents;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

final class ComponentTokenParser extends IncludeTokenParser
{
    /**
     * @var String Component tag name.
     */
    private $tag;

    /**
     * @var String Directory for the components files.
     */
    private $path;

    /**
     * ComponentTokenParser constructor.
     * @param string $tag
     * @param string $path
     */
    public function __construct(string $tag, string $path)
    {
        $this->tag = $tag;
        $this->path = $path;
    }

    public function getComponentPath()
    {
        return rtrim($this->path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $this->tag . '.twig';
    }

    public function parse(Token $token): Node
    {
        list($variables) = $this->parseArguments();

        $slot = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new ComponentNode($this->getComponentPath(), $slot, $variables, $token->getLine());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $variables = null;

        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        return [$variables];
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('end' . $this->tag);
    }

    public function getTag(): string
    {
        return $this->tag;
    }
}
