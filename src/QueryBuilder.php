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
use CallbackFilterIterator;
use InvalidArgumentException;
use Iterator;
use LimitIterator;

/**
 *  A Trait to Query rows against a SplFileObject
 *
 * @package p.iterators
 * @since  0.3.0
 *
 */
trait QueryBuilder
{
    /**
     * Callable function to filter the iterator
     *
     * @var array
     */
    protected $iterator_where = [];

    /**
     * Callable function to sort the ArrayObject
     *
     * @var array
     */
    protected $iterator_orderby = [];

    /**
     * iterator Offset
     *
     * @var integer
     */
    protected $iterator_offset = 0;

    /**
     * iterator maximum length
     *
     * @var integer
     */
    protected $iterator_limit = -1;

    /**
     * iterator select
     *
     * @var callable
     */
    protected $iterator_select;

    /**
     * Return a filtered Iterator based on the filtering settings
     *
     * @param Iterator $iterator The iterator to be filtered
     * @param callable $callable a callable function to be applied to each Iterator item
     *
     * @return Iterator
     */
    public function query()
    {
        $iterator = $this->getIterator();
        $iterator = $this->applyIteratorWhere($iterator);
        $iterator = $this->applyIteratorOrderBy($iterator);
        $iterator = $this->applyIteratorInterval($iterator);
        $iterator = $this->applyIteratorSelect($iterator);

        return $iterator;
    }

    /**
     * Return an Iterator
     *
     * @return \Iterator
     */
    abstract public function getIterator();

    /**
    * Filter the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applyIteratorWhere(Iterator $iterator)
    {
        foreach ($this->iterator_where as $callable) {
            $iterator = new CallbackFilterIterator($iterator, $callable);
        }
        $this->clearWhere();

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
        $this->iterator_where[] = $callable;

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
        $res = array_search($callable, $this->iterator_where, true);
        if (false !== $res) {
            unset($this->iterator_where[$res]);
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
        return false !== array_search($callable, $this->iterator_where, true);
    }

    /**
     * Remove all registered callable filter
     *
     * @return $this
     */
    public function clearWhere()
    {
        $this->iterator_where = [];

        return $this;
    }

    /**
    * Sort the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \ArrayIterator
    */
    protected function applyIteratorOrderBy(Iterator $iterator)
    {
        if (! $this->iterator_orderby) {
            return $iterator;
        }
        $nb_callbacks = count($this->iterator_orderby);
        $this->iterator_orderby = array_values($this->iterator_orderby);
        $res = iterator_to_array($iterator, false);
        uasort($res, function ($rowA, $rowB) use ($nb_callbacks) {
            $res   = 0;
            $index = 0;
            while ($index < $nb_callbacks && 0 === $res) {
                $callable = $this->iterator_orderby[$index];
                $res = $callable($rowA, $rowB);
                ++$index;
            }

            return $res;
        });
        $this->clearOrderBy();

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
        $this->iterator_orderby[] = $callable;

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
        $res = array_search($callable, $this->iterator_orderby, true);
        if (false !== $res) {
            unset($this->iterator_orderby[$res]);
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
        return false !== array_search($callable, $this->iterator_orderby, true);
    }

    /**
     * Remove all registered callable
     *
     * @return $this
     */
    public function clearOrderBy()
    {
        $this->iterator_orderby = [];

        return $this;
    }

    /**
    * Sort the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \LimitIterator
    */
    protected function applyIteratorInterval(Iterator $iterator)
    {
        if (0 == $this->iterator_offset && -1 == $this->iterator_limit) {
            return $iterator;
        }
        $offset = $this->iterator_offset;
        $limit = $this->iterator_limit;

        $this->iterator_limit = -1;
        $this->iterator_offset = 0;

        return new LimitIterator($iterator, $offset, $limit);
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
        $this->iterator_offset = $offset;

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
        $this->iterator_limit = $limit;

        return $this;
    }

    /**
    * Select part of the Iterator
    *
    * @param \Iterator $iterator
    *
    * @return \Iterator
    */
    protected function applyIteratorSelect(Iterator $iterator)
    {
        if (is_null($this->iterator_select)) {
            return $iterator;
        }
        $callable = $this->iterator_select;

        return new MapIterator($iterator, $callable);
    }

    /**
     * Set the Select callable function
     *
     * @param callable $callable
     *
     * @return self
     */
    public function setSelect($callable)
    {
        $this->iterator_select = $callable;

        return $this;
    }

    /**
     * Remove all registered callable filter
     *
     * @return $this
     */
    public function clearSelect()
    {
        $this->iterator_select = null;

        return $this;
    }
}
