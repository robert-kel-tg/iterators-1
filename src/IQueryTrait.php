<?php

namespace P\IQuery;

use Iterator;
use P\IQuery\SQL\Where;
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
    use Where;

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
    public function queryIterator()
    {
        $iterator = $this->getIterator();
        $iterator = $this->applyWhere($iterator);
        $iterator = $this->applySortBy($iterator);
        $iterator = $this->applyInterval($iterator);
        $iterator = $this->applySelect($iterator);

        return $iterator;
    }
}
