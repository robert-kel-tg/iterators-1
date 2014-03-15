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
     * @param callable $filter
     *
     * @return self
     */
    public function addWhere(callable $callable, $is_recursive = false)
    {
        $is_recursive = filter_var($is_recursive, FILTER_VALIDATE_BOOLEAN);
        $this->filter[] = [$is_recursive, $callable];

        return $this;
    }

    /**
     * Remove a filter from the callable collection
     *
     * @param callable $callable
     *
     * @return self
     */
    public function removeWhere(callable $callable, $is_recursive = false)
    {
        $is_recursive = filter_var($is_recursive, FILTER_VALIDATE_BOOLEAN);
        $res = array_search([$is_recursive, $callable], $this->filter, true);
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
    public function hasWhere(callable $callable, $is_recursive = false)
    {
        $is_recursive = filter_var($is_recursive, FILTER_VALIDATE_BOOLEAN);
        return false !== array_search([$is_recursive, $callable], $this->filter, true);
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
        foreach ($this->filter as $where) {
            list($is_recursive, $callable) = $where;
            $filter = 'CallbackFilterIterator';
            if ($is_recursive) {
                $filter = 'RecursiveCallbackFilterIterator '; 
            }
            $iterator = new $filter($iterator, $callable);
        }
        $this->clearWhere();

        return $iterator;
    }
}
