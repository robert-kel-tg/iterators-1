<?php
/**
* This file is part of the P\Iterator library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/iterator/
* @version 0.4.0
* @package nyamsprod.iterators
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace Nyamsprod\Iterators;

use ArrayObject;
use CallbackFilterIterator;
use InvalidArgumentException;
use Iterator;
use IteratorAggregate;
use LimitIterator;

/**
 *  A Class to Query in a SQL-like manner Iterators
 *
 * @package p.iterators
 * @since 0.1.0
 *
 */
class QueryIterator implements IteratorAggregate
{
   /**
     * The Iterator to Query on
     *
     * @var \Iterator
     */
    protected $iterator;

    /**
     * Callable function to filter the iterator
     *
     * @var array
     */
    protected $filter = [];

    /**
     * Callable function to sort the ArrayObject
     *
     * @var array
     */
    protected $sortby = [];

    /**
     * iterator Offset
     *
     * @var integer
     */
    protected $offset = 0;

    /**
     * iterator maximum length
     *
     * @var integer
     */
    protected $limit = -1;

    /**
     * iterator select
     *
     * @var callable
     */
    protected $select;

    /**
     * The Constructor
     *
     * @param \Iterator $iterator
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * Set the Iterator filter method
     *
     * @param callable $callable
     *
     * @return static
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
     * @return static
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
     * @return static
     */
    public function clearFilter()
    {
        $this->filter = [];

        return $this;
    }

    /**
     * Set an Iterator sorting callable function
     *
     * @param callable $callable
     *
     * @return static
     */
    public function addSortBy(callable $callable)
    {
        $this->sortby[] = $callable;

        return $this;
    }

    /**
     * Remove a callable from the collection
     *
     * @param callable $callable
     *
     * @return static
     */
    public function removeSortBy(callable $callable)
    {
        $res = array_search($callable, $this->sortby, true);
        if (false !== $res) {
            unset($this->sortby[$res]);
            $this->sortby = array_values($this->sortby);
        }

        return $this;
    }

    /**
     * Detect if the callable is already registered
     *
     * @param callable $callable
     *
     * @return boolean
     */
    public function hasSortBy(callable $callable)
    {
        return false !== array_search($callable, $this->sortby, true);
    }

    /**
     * Remove all registered callable
     *
     * @return static
     */
    public function clearSortBy()
    {
        $this->sortby = [];

        return $this;
    }

    /**
     * Set LimitIterator Offset
     *
     * @param $offset
     *
     * @return static
     */
    public function setOffset($offset = 0)
    {
        if (false === filter_var($offset, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) {
            throw new InvalidArgumentException('the offset must be a positive integer or 0');
        }
        $this->offset = $offset;

        return $this;
    }

    /**
     * Return the current query offset setting
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set LimitInterator Count
     *
     * @param integer $limit
     *
     * @return static
     */
    public function setLimit($limit = -1)
    {
        if (false === filter_var($limit, FILTER_VALIDATE_INT, ['options' => ['min_range' => -1]])) {
            throw new InvalidArgumentException('the limit must an integer greater or equals to -1');
        }
        $this->limit = $limit;

        return $this;
    }

    /**
     * Return the current query limit setting
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the Select callable function
     *
     * @param callable $callable
     *
     * @return static
     */
    public function setSelect(callable $callable = null)
    {
        $this->select = $callable;

        return $this;
    }

    /**
     * Get the current select filter
     *
     * @return callable
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * Return a filtered Iterator based on the filtering settings
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $iterator = $this->applyFilter($this->iterator);
        $iterator = $this->applySortBy($iterator);
        $iterator = $this->applyInterval($iterator);

        return $this->applySelect($iterator);
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

        return $iterator;
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
        if (! $this->sortby) {
            return $iterator;
        }
        $rules = count($this->sortby);
        $arr = new ArrayObject(iterator_to_array($iterator));
        $arr->uasort(function ($rowA, $rowB) use ($rules) {
            $res   = 0;
            $index = 0;
            while ($index < $rules && 0 === $res) {
                $callable = $this->sortby[$index];
                $res = $callable($rowA, $rowB);
                ++$index;
            }

            return $res;
        });

        return $arr->getIterator();
    }

    /**
    * Sort the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \LimitIterator
    */
    protected function applyInterval(Iterator $iterator)
    {
        if (0 == $this->offset && -1 == $this->limit) {
            return $iterator;
        }

        return new LimitIterator($iterator, $this->offset, $this->limit);
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

        return new MapIterator($iterator, $this->select);
    }

    /**
     * Remove all registered options
     *
     * @return static
     */
    public function clearAll()
    {
        $this->clearFilter();
        $this->clearSortBy();
        $this->offset = 0;
        $this->limit  = -1;
        $this->select = null;

        return $this;
    }

    /**
     * Apply a callback function on each item from the Iterator
     *
     * The callback function must return true in order to continue
     * iterating over the Iterator
     *
     * @param callable $callable
     *
     * @return integer the iteration count
     */
    public function each(callable $callable)
    {
        $index    = 0;
        $iterator = $this->getIterator();
        $iterator->rewind();
        while ($iterator->valid() && true === $callable($iterator->current(), $iterator->key(), $iterator)) {
            ++$index;
            $iterator->next();
        }

        return $index;
    }
}
