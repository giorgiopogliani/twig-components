<?php


namespace Performing\TwigComponents;

use Closure;

class SlotClosure
{
    private Closure $slot;

    public function __construct(Closure $slot)
    {
        $this->slot = $slot;
    }

    public function __toString()
    {
        ob_start();

        call_user_func($this->slot);

        return ob_get_clean();
    }
}
