<?php

namespace P\IQuery\SQL;

use InvalidArgumentException;
use Iterator;
use LimitIterator;

/**
 *  A Trait to Set a LimitIterator object
 */
trait Interval
{
    /**
     * iterator Offset
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * iterator maximum length
     *
     * @var integer
     */
    private $limit = -1;

    /**
     * Set LimitIterator Offset
     *
     * @param $offset
     *
     * @return self
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
     * @return self
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
        $offset = $this->offset;
        $limit = $this->limit;

        $this->limit = -1;
        $this->offset = 0;

        return new LimitIterator($iterator, $offset, $limit);
    }
}
