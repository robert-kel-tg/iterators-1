<?php

namespace P\IQuery\test;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use P\IQuery\IQuery;

/**
 * @group iterator
 */
class IQueryTest extends PHPUnit_Framework_TestCase
{
    private $stmt;
    private $iterator;
    private $data = ['john', 'jane', 'foo', 'bar'];

    public function setUp()
    {
        $this->stmt = new Iquery(new ArrayIterator($this->data));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetLimit()
    {
        $this->stmt->setLimit(1);
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator);
        $this->assertCount(1, $res);

        $this->stmt->setLimit(-4);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetOffset()
    {
        $this->stmt->setOffset(1);
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator);
        $this->assertCount(3, $res);

        $this->stmt->setOffset('toto');
    }

    public function testIntervalLimitTooLong()
    {
        $this->stmt->setOffset(3);
        $this->stmt->setLimit(10);
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator);
        $this->assertSame([3 => 'bar'], $res);
        $this->assertCount(1, $res);
    }

    public function testInterval()
    {
        $this->stmt->setOffset(1);
        $this->stmt->setLimit(1);
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator);
        $this->assertCount(1, $res);
    }

    public function testFilter()
    {
        $func = function ($row) {
            return false !== strpos($row, 'o');
        };
        $this->stmt->setFilter($func);

        $iterator =$this->stmt->query();
        $this->assertCount(2, iterator_to_array($iterator, false));

        $func2 = function ($row) {
            return false !== strpos($row, 'j');
        };
        $this->stmt->addFilter($func2);
        $this->stmt->addFilter($func);

        $iterator = $this->stmt->query();
        $this->assertCount(1, iterator_to_array($iterator, false));

        $this->stmt->addFilter($func2);
        $this->stmt->addFilter($func);
        $this->assertTrue($this->stmt->hasFilter($func2));
        $this->stmt->removeFilter($func2);
        $this->assertFalse($this->stmt->hasFilter($func2));

        $iterator = $this->stmt->query();
        $this->assertCount(2, iterator_to_array($iterator, false));
    }

    public function testSortBy()
    {
        $this->stmt->setSortBy('strcmp');
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator, false);
        $this->assertSame(['bar', 'foo', 'jane', 'john'], $res);

        $this->stmt->addSortBy('strcmp');
        $this->stmt->addSortBy('strcmp');
        $this->stmt->removeSortBy('strcmp');
        $this->assertTrue($this->stmt->hasSortBy('strcmp'));
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator, false);
        $this->assertSame(['bar', 'foo', 'jane', 'john'], $res);
    }

    public function testExecuteWithCallback()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $iterator = $this->stmt->query();
        $this->assertSame(array_map('strtoupper', $this->data), iterator_to_array($iterator));
    }
}
