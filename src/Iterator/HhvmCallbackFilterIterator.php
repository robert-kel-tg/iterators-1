<?php

namespace P\IQuery\Iterator;

use FilterIterator;
use Iterator;

/**
 *  A simple MapIterator
 */
class HhvmCallbackFilterIterator extends FilterIterator
{
    /**
     * The function to be apply on all InnerIterator element
     *
     * @var callable
     */
    protected $callable;

    /**
     * The Constructor
     *
     * @param Traversable $iterator
     * @param callable    $callable
     */
    public function __construct(Iterator $iterator, callable $callable)
    {
        parent::__construct($iterator);
        $this->callable = $callable;
    }

    /**
     * Get the value of the current element
     */
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        $callable = $this->callable;

        return $callable($iterator->current(), $iterator->key(), $iterator);
    }
}
