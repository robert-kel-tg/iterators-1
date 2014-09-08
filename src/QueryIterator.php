<?php
/**
* This file is part of the P\IQuery library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/IQuery/
* @version 0.2.0
* @package League.csv
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace P\IQuery;

use Iterator;
use IteratorAggregate;

/**
 *  A Class to Query in a SQL-like manner Iterators
 */
class QueryIterator implements IteratorAggregate
{
    /**
     *  Iterator Set Interval Trait
     */
    use IteratorQueryBuilder;

   /**
     * The Iterator to Query on
     *
     * @var \Iterator
     */
    protected $iterator;

    /**
     * The Constructor
     *
     * @param Traversable $iterator
     * @param callable    $callable
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
