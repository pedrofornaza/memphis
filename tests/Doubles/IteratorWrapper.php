<?php

namespace Doubles;

use Iterator;

class IteratorWrapper
{
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    public function getIterator()
    {
        return $this->iterator;
    }
}
