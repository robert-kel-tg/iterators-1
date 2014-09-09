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

use Iterator;
use IteratorAggregate;

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
     *  Iterator Set Interval Trait
     */
    use QueryExtract;

   /**
     * The Iterator to Query on
     *
     * @var \Iterator
     */
    protected $iterator;

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
     * Implements the IteratorAggregate Interface
     * @return \Iterator
     */
    public function getIterator()
    {
        return $this->iterator;
    }
}
