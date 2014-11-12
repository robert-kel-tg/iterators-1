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
     * A Trait that add all the major functionality
     */
    use QueryIteratorTrait;

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
}
