<?php

namespace P\IQuery\SQL;

use CallbackFilterIterator;
use Iterator;

/**
 *  A Trait to filter Iterators
 */
trait Where
{
    /**
     * Callable function to filter the iterator
     *
     * @var array
     */
    private $filter = [];

    /**
     * Set the Iterator filter method
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 5.1
     *
     * @param callable $callable
     *
     * @return self
     */
    public function setWhere(callable $callable)
    {
        return $this->addWhere($callable);
    }

    /**
     * Set the Iterator filter method
     *
     * @param callable $filter
     *
     * @return self
     */
    public function addWhere(callable $callable)
    {
        $this->filter[] = $callable;

        return $this;
    }

    /**
     * Remove a filter from the callable collection
     *
     * @param callable $callable
     *
     * @return self
     */
    public function removeWhere(callable $callable)
    {
        $res = array_search($callable, $this->filter, true);
        if (false !== $res) {
            unset($this->filter[$res]);
        }

        return $this;
    }

    /**
     * Detect if the callable filter is already registered
     *
     * @param callable $callable
     *
     * @return boolean
     */
    public function hasWhere(callable $callable)
    {
        return false !== array_search($callable, $this->filter, true);
    }

    /**
     * Remove all registered callable filter
     *
     * @return self
     */
    public function clearWhere()
    {
        $this->filter = [];

        return $this;
    }

    /**
    * Where the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applyWhere(Iterator $iterator)
    {
        foreach ($this->filter as $callable) {
            $iterator = new CallbackFilterIterator($iterator, $callable);
        }
        $this->clearWhere();

        return $iterator;
    }
}
