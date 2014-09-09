<?php
/**
* This file is part of the P\Iterator library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/nyamsprod/Iterator/
* @version 0.2.0
* @package League.csv
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
namespace P\Iterators;

use IteratorIterator;
use Traversable;

/**
 *  A simple MapIterator
 */
class MapIterator extends IteratorIterator
{
    /**
     * The function to be apply on all InnerIterator element
     *
     * @var callable
     */
    private $callable;

    /**
     * The Constructor
     *
     * @param Traversable $iterator
     * @param callable    $callable
     */
    public function __construct(Traversable $iterator, callable $callable)
    {
        parent::__construct($iterator);
        $this->callable = $callable;
    }

    /**
     * Get the value of the current element
     */
    public function current()
    {
        $iterator = $this->getInnerIterator();
        $callable = $this->callable;

        return $callable($iterator->current(), $iterator->key(), $iterator);
    }
}
