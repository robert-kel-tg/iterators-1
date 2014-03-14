<?php

namespace P\IQuery;

use Iterator;
use P\IQuery\SQL\Filter;
use P\IQuery\SQL\SortBy;
use P\IQuery\SQL\Interval;
use P\IQuery\SQL\Select;

/**
 *  A Trait to Query in a SQL-like manner Iterators
 */
trait IQueryTrait
{
    /**
     *  Iterator Filtering Trait
     */
    use Filter;

    /**
     *  Iterator Sorting Trait
     */
    use SortBy;

    /**
     *  Iterator Set Interval Trait
     */
    use Interval;

    /**
     *  Iterator Set Interval Trait
     */
    use Select;

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
        $iterator = $this->applyFilter($iterator);
        $iterator = $this->applySortBy($iterator);
        $iterator = $this->applyInterval($iterator);
        $iterator = $this->applySelect($iterator);

        return $iterator;
    }
}
