<?php

namespace P\IQuery;

use Iterator;

/**
 *  A Class to Query in a SQL-like manner Iterators
 */
class IQuery implements IteratorAggregate
{
    /**
     *  Iterator Set Interval Trait
     */
    use IQueryTrait;

   /**
     * The Iterator to Query on
     *
     * @var \Iterator
     */
    private $iterator;

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

    public function getIterator()
    {
        return $this->iterator;
    }
}
