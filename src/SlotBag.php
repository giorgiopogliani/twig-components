<?php

namespace Performing\TwigComponents;

class SlotBag
{
    private $slot;

    public function __construct(string $slot)
    {
        $this->slot = $slot;
    }

    public function __toString()
    {
        return $this->slot;
    }
}
