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

use FilterIterator;
use Iterator;
use OuterIterator;

/**
 *  A simple MapIterator
 */
class CallbackFilterIterator extends FilterIterator implements OuterIterator
{
    /**
     * The function to filter out the InnerIterator element
     * The function can take up to three arguments:
     *  - the current value
     *  - the current key
     *  - the crrent inner iterator
     *
     * @var callable
     */
    protected $callable;

    /**
     * The Constructor
     *
     * @param Traversable $iterator
     * @param callable    $callable
     */
    public function __construct(Iterator $iterator, callable $callable)
    {
        parent::__construct($iterator);
        $this->callable = $callable;
    }

    /**
     * Returns whether the current element of the iterator is acceptable through this filter.
     *
     * @return boolean (true if the current element is acceptable, otherwise false)
     */
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        $callable = $this->callable;

        return $callable($iterator->current(), $iterator->key(), $iterator);
    }
}
