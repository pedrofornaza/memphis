<?php

namespace Doubles;

use ArrayIterator;

class ArrayIteratorWrapper
{
    public function __construct(ArrayIterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator()
    {
        return $this->iterator;
    }
}
