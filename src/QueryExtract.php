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

use InvalidArgumentException;
use Iterator;

/**
 *  A Trait to Query rows against a Iterator
 *
 * @package p.iterators
 * @since 0.3.0
 *
 */
trait QueryExtract
{

    /**
     * Trait to issue a SQL like Query on a Iterator
     */
    use QueryBuilder;

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
        $index = 0;
        $iterator = $this->query();
        $iterator->rewind();
        while ($iterator->valid() && true === $callable($iterator->current(), $iterator->key(), $iterator)) {
            ++$index;
            $iterator->next();
        }

        return $index;
    }

    /**
     * Return a single item from the Iterator
     *
     * @param integer $offset
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException If $offset is not a valid Integer
     */
    public function fetchOne($offset = 0)
    {
        $this->setOffset($offset);
        $this->setLimit(1);
        $iterator = $this->query();
        $iterator->rewind();

        return $iterator->current();
    }

    /**
     * Return a sequential array of all Iterator items
     *
     * @return array
     */
    public function fetchAll()
    {
        return iterator_to_array($this->query(), false);
    }
}
