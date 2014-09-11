<?php
/**
* This file is part of the P\Iterator library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/Iterator/
* @version 0.3.0
* @package p.iterators
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace P\Iterators;

use ArrayIterator;
use Iterator;
use IteratorAggregate;
use LimitIterator;
use CallbackFilterIterator;
use InvalidArgumentException;

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
    protected $where = [];

    /**
     * Callable function to sort the ArrayObject
     *
     * @var array
     */
    protected $orderby = [];

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
     * @param callable  $callable
     */
    public function __construct(Iterator $iterator)
    {
        $this->iterator = $iterator;
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

    /**
     * Return a filtered Iterator based on the filtering settings
     *
     * @param Iterator $iterator The iterator to be filtered
     * @param callable $callable a callable function to be applied to each Iterator item
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $iterator = $this->applyWhere($this->iterator);
        $iterator = $this->applyOrderBy($iterator);
        $iterator = $this->applyInterval($iterator);
        $iterator = $this->applySelect($iterator);

        return $iterator;
    }

    /**
    * Filter the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applyWhere(Iterator $iterator)
    {
        foreach ($this->where as $callable) {
            $iterator = new CallbackFilterIterator($iterator, $callable);
        }

        return $iterator;
    }

    /**
     * Set the Iterator filter method
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function addWhere(callable $callable)
    {
        $this->where[] = $callable;

        return $this;
    }

    /**
     * Remove a filter from the callable collection
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function removeWhere(callable $callable)
    {
        $res = array_search($callable, $this->where, true);
        if (false !== $res) {
            unset($this->where[$res]);
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
        return false !== array_search($callable, $this->where, true);
    }

    /**
     * Remove all registered callable filter
     *
     * @return $this
     */
    public function clearWhere()
    {
        $this->where = [];

        return $this;
    }

    /**
    * Sort the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \ArrayIterator
    */
    protected function applyOrderBy(Iterator $iterator)
    {
        if (! $this->orderby) {
            return $iterator;
        }
        $nb_callbacks = count($this->orderby);
        $this->orderby = array_values($this->orderby);
        $res = iterator_to_array($iterator, false);
        uasort($res, function ($rowA, $rowB) use ($nb_callbacks) {
            $res   = 0;
            $index = 0;
            while ($index < $nb_callbacks && 0 === $res) {
                $callable = $this->orderby[$index];
                $res = $callable($rowA, $rowB);
                ++$index;
            }

            return $res;
        });

        return new ArrayIterator($res);
    }

    /**
     * Set an Iterator sorting callable function
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function addOrderBy(callable $callable)
    {
        $this->orderby[] = $callable;

        return $this;
    }

    /**
     * Remove a callable from the collection
     *
     * @param callable $callable
     *
     * @return $this
     */
    public function removeOrderBy(callable $callable)
    {
        $res = array_search($callable, $this->orderby, true);
        if (false !== $res) {
            unset($this->orderby[$res]);
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
    public function hasOrderBy(callable $callable)
    {
        return false !== array_search($callable, $this->orderby, true);
    }

    /**
     * Remove all registered callable
     *
     * @return $this
     */
    public function clearOrderBy()
    {
        $this->orderby = [];

        return $this;
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
     * Set LimitIterator Offset
     *
     * @param $offset
     *
     * @return $this
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
     * Set LimitInterator Count
     *
     * @param integer $limit
     *
     * @return $this
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
     * Set the Select callable function
     *
     * @param callable $callable
     *
     * @return self
     */
    public function setSelect(callable $callable = null)
    {
        $this->select = $callable;

        return $this;
    }

    /**
     * Remove all registered options
     *
     * @return $this
     */
    public function clear()
    {
        $this->clearWhere();
        $this->clearOrderBy();
        $this->limit  = -1;
        $this->offset = 0;
        $this->select = null;

        return $this;
    }
}
