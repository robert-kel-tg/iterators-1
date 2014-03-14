<?php

namespace P\IQuery\SQL;

use CallbackFilterIterator;
use Iterator;

/**
 *  A Trait to filter Iterators
 */
trait Filter
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
    public function setFilter(callable $callable)
    {
        return $this->addFilter($callable);
    }

    /**
     * Set the Iterator filter method
     *
     * @param callable $filter
     *
     * @return self
     */
    public function addFilter(callable $callable)
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
    public function removeFilter(callable $callable)
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
    public function hasFilter(callable $callable)
    {
        return false !== array_search($callable, $this->filter, true);
    }

    /**
     * Remove all registered callable filter
     *
     * @return self
     */
    public function clearFilter()
    {
        $this->filter = [];

        return $this;
    }

    /**
    * Filter the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applyFilter(Iterator $iterator)
    {
        foreach ($this->filter as $callable) {
            $iterator = new CallbackFilterIterator($iterator, $callable);
        }
        $this->clearFilter();

        return $iterator;
    }
}
