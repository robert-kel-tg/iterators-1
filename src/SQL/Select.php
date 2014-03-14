<?php

namespace P\IQuery\SQL;

use Iterator;
use LimitIterator;
use P\IQuery\Iterator\MapIterator;

/**
 *  A Trait to Set a LimitIterator object
 */
trait Select
{
    /**
     * iterator Offset
     *
     * @var integer
     */
    private $select;

    /**
     * Set the Select callable function
     *
     * @param callable $callable
     *
     * @return self
     */
    public function setSelect($callable)
    {
        $this->select = $callable;

        return $this;
    }

    /**
    * Select part of the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applySelect(Iterator $iterator)
    {
        if (is_null($this->select)) {
            return $iterator;
        }
        $callable = $this->select;

        return new MapIterator($iterator, $callable);
    }
}
