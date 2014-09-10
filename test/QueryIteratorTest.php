<?php

namespace P\Iterators\test;

use ArrayIterator;
use PHPUnit_Framework_TestCase;
use P\Iterators\QueryIterator;

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

    public function testWhere()
    {
        $func = function ($row) {
            return false !== strpos($row, 'o');
        };
        $this->stmt->addWhere($func);

        $iterator =$this->stmt->query();
        $this->assertCount(2, iterator_to_array($iterator, false));

        $func2 = function ($row) {
            return false !== strpos($row, 'j');
        };
        $this->stmt->addWhere($func2);
        $this->stmt->addWhere($func);

        $iterator = $this->stmt->query();
        $this->assertCount(1, iterator_to_array($iterator, false));

        $this->stmt->addWhere($func2);
        $this->stmt->addWhere($func);
        $this->assertTrue($this->stmt->hasWhere($func2));
        $this->stmt->removeWhere($func2);
        $this->assertFalse($this->stmt->hasWhere($func2));

        $iterator = $this->stmt->query();
        $this->assertCount(2, iterator_to_array($iterator, false));
    }

    public function testOrderBy()
    {
        $this->stmt->addOrderBy('strcmp');
        $iterator = $this->stmt->query();
        $res = iterator_to_array($iterator, false);
        $this->assertSame(['bar', 'foo', 'jane', 'john'], $res);

        $this->stmt->addOrderBy('strcmp');
        $this->stmt->addOrderBy('strcmp');
        $this->stmt->removeOrderBy('strcmp');
        $this->assertTrue($this->stmt->hasOrderBy('strcmp'));
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

    public function testSelectWhenCleared()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $this->stmt->setSelect();
        $iterator = $this->stmt->query();
        $this->assertSame($this->data, iterator_to_array($iterator));
    }

    public function testClearAll()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $this->stmt->addOrderBy('strcmp');
        $func = function ($row) {
            return false !== strpos($row, 'o');
        };
        $this->stmt->addWhere($func);
        $this->stmt->setOffSet(10);
        $this->stmt->setLimit(20);
        $this->stmt->clear();
        $this->assertSame($this->data, $this->stmt->fetchAll());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testFetchOne()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $item = $this->stmt->fetchOne();
        $this->assertSame('JOHN', $item);

        $this->stmt->setSelect($func);
        $this->stmt->fetchOne(-3);
    }

    public function testFetchAll()
    {
        $func = function ($value) {
            return strtoupper($value);
        };

        $this->stmt->setSelect($func);
        $res = $this->stmt->fetchAll();
        $this->assertSame(array_values(array_map('strtoupper', $this->data)), $res);
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
