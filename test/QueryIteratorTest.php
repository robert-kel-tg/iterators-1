<?php

namespace P\Iterators\test;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use Nyamsprod\Iterators\QueryIterator;

/**
 * @group iterator
 */
class QueryIteratorTest extends PHPUnit_Framework_TestCase
{
    private $stmt;
    private $data = ['john', 'jane', 'foo', 'bar'];

    public function setUp()
    {
        $this->stmt = new QueryIterator(new ArrayIterator($this->data));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetLimit()
    {
        $this->stmt->setLimit(1);
        $res = iterator_to_array($this->stmt);
        $this->assertCount(1, $res);

        $this->stmt->setLimit(-4);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetOffset()
    {
        $this->stmt->setOffset(1);
        $res = iterator_to_array($this->stmt);
        $this->assertCount(3, $res);

        $this->stmt->setOffset('toto');
    }

    public function testIntervalLimitTooLong()
    {
        $this->stmt->setOffset(3);
        $this->stmt->setLimit(10);
        $res = iterator_to_array($this->stmt);
        $this->assertSame(10, $this->stmt->getLimit());
        $this->assertSame(3, $this->stmt->getOffset());
        $this->assertSame([3 => 'bar'], $res);
        $this->assertCount(1, $res);
    }

    public function testInterval()
    {
        $this->stmt->setOffset(1);
        $this->stmt->setLimit(1);
        $res = iterator_to_array($this->stmt);
        $this->assertCount(1, $res);
    }

    public function testFilter()
    {
        $func = function ($row) {
            return false !== strpos($row, 'o');
        };
        $this->stmt->addFilter($func);

        $this->assertCount(2, iterator_to_array($this->stmt, false));

        $func2 = function ($row) {
            return false !== strpos($row, 'j');
        };
        $this->stmt->addFilter($func2);
        $this->assertCount(1, iterator_to_array($this->stmt, false));

        $this->assertTrue($this->stmt->hasFilter($func2));
        $this->stmt->removeFilter($func2);
        $this->assertFalse($this->stmt->hasFilter($func2));

        $this->assertCount(2, iterator_to_array($this->stmt, false));
    }

    public function testSortBy()
    {
        $this->stmt->addSortBy('strcmp');
        $res = iterator_to_array($this->stmt, false);
        $this->assertSame(['bar', 'foo', 'jane', 'john'], $res);

        $this->stmt->addSortBy('strcmp');
        $this->stmt->addSortBy('strcmp');
        $this->stmt->removeSortBy('strcmp');
        $this->assertTrue($this->stmt->hasSortBy('strcmp'));
        $res = iterator_to_array($this->stmt, false);
        $this->assertSame(['bar', 'foo', 'jane', 'john'], $res);
    }

    public function testExecuteWithCallback()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $this->assertSame(array_map('strtoupper', $this->data), iterator_to_array($this->stmt));
    }

    public function testSelectWhenCleared()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $this->assertSame($func, $this->stmt->getSelect());
        $this->stmt->setSelect();
        $this->assertSame($this->data, iterator_to_array($this->stmt));
    }

    public function testClearAll()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $this->stmt->addSortBy('strcmp');
        $func = function ($row) {
            return false !== strpos($row, 'o');
        };
        $this->stmt->addFilter($func);
        $this->stmt->setOffSet(10);
        $this->stmt->setLimit(20);
        $this->stmt->clearAll();
        $this->assertSame($this->data, iterator_to_array($this->stmt));
    }

    public function testEach()
    {
        $transform = [];
        $res = $this->stmt->each(function ($row) use (&$transform) {
            $transform[] = strtoupper($row);

            return true;
        });
        $this->assertSame($res, 4);
        $this->assertSame(strtoupper($this->data[0]), $transform[0]);
        $res = $this->stmt->each(function ($row, $index) {
            if ($index > 0) {
                return false;
            }

            return true;
        });
        $this->assertSame($res, 1);
    }
}
