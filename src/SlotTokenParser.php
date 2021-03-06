<?php

namespace Performing\TwigComponents;

use Exception;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\IncludeTokenParser;

final class SlotTokenParser extends IncludeTokenParser
{
    public function parse(Token $token): Node
    {
        $name = $this->parseArguments();

        $slot = $this->parser->subparse([$this, 'decideBlockEnd'], true);

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new SlotNode($name, $slot, $token->getLine());
    }

    protected function parseArguments()
    {
        $stream = $this->parser->getStream();

        $name = null;

        if ($stream->nextIf(Token::PUNCTUATION_TYPE, ':')) {
            $name = $this->parseSlotName();
        }

        $stream->expect(/* Token::BLOCK_END_TYPE */3);

        return $name;
    }

    public function parseSlotName(): string
    {
        $stream = $this->parser->getStream();

        if ($this->parser->getCurrentToken()->getType() != /** Token::NAME_TYPE */ 5) {
            throw new Exception('First token must be a name type');
        }

        return $stream->next()->getValue();
    }

    public function decideBlockEnd(Token $token): bool
    {
        return $token->test('endslot');
    }

    public function getTag(): string
    {
        return 'slot';
    }
}
