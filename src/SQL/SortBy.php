<?php

namespace P\IQuery\SQL;

use Iterator;
use ArrayIterator;

/**
 *  A Trait to sort an Iterator
 */
trait SortBy
{
    /**
     * Callable function to sort the ArrayObject
     *
     * @var callable
     */
    private $sortBy = [];

    /**
     * Set the Iterator SortBy method
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated deprecated since version 5.2
     *
     * @param callable $callable
     *
     * @return self
     */
    public function setSortBy(callable $callable)
    {
        return $this->addSortBy($callable);
    }

    /**
     * Set an Iterator sortBy method
     *
     * @param callable $filter
     *
     * @return self
     */
    public function addSortBy(callable $callable)
    {
        $this->sortBy[] = $callable;

        return $this;
    }

    /**
     * Remove a callable from the collection
     *
     * @param callable $filter
     *
     * @return self
     */
    public function removeSortBy(callable $callable)
    {
        $res = array_search($callable, $this->sortBy, true);
        if (false !== $res) {
            unset($this->sortBy[$res]);
        }

        return $this;
    }

    /**
     * Detect if the callable is already registered
     *
     * @param callable $filter
     *
     * @return boolean
     */
    public function hasSortBy(callable $callable)
    {
        return false !== array_search($callable, $this->sortBy, true);
    }

    /**
     * Remove all registered callable
     *
     * @return self
     */
    public function clearSortBy()
    {
        $this->sortBy = [];

        return $this;
    }

    /**
    * Sort the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \ArrayIterator
    */
    protected function applySortBy(Iterator $iterator)
    {
        if (! $this->sortBy) {
            return $iterator;
        }
        $res = iterator_to_array($iterator, false);

        uasort($res, function ($rowA, $rowB) {
            foreach ($this->sortBy as $callable) {
                $res = $callable($rowA, $rowB);
                if (0 !== $res) {
                    break;
                }
            }

            return $res;
        });

        $this->clearSortBy();

        return new ArrayIterator($res);
    }
}
