<?php

namespace Bardex\Tests;

use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;


class SortWithLimitTest extends AbstractTestCase
{
    protected static $testdata = [
        ['id' => 2,  'status' => 200],
        ['id' => 20, 'status' => 100],
        ['id' => 10, 'status' => 200],
        ['id' => 30, 'status' => 100]
    ];

    public function testOrderAsc()
    {
        $query = $this->createQuery();
        $query->setOrderBy('id', 'asc');
        $query->limit(2,1);

        $result = $query->fetchAll();

        $this->assertCount(2, $result);
        $this->assertEquals(10, $result[0]['id']);
        $this->assertEquals(20, $result[1]['id']);
    }

    public function testOrderDesc()
    {
        $query = $this->createQuery();
        $query->setOrderBy('id', 'desc');
        $query->limit(2,1);

        $result = $query->fetchAll();

        $this->assertCount(2, $result);
        $this->assertEquals(20, $result[0]['id']);
        $this->assertEquals(10, $result[1]['id']);
    }

    public function testMultiOrder()
    {
        $query = $this->createQuery();
        $query->setOrderBy('status', 'asc');
        $query->addOrderBy('id', 'asc');

        $result = $query->fetchAll();

        $this->assertCount(4, $result);
        $this->assertEquals(20, $result[0]['id']);
        $this->assertEquals(30, $result[1]['id']);
        $this->assertEquals(2,  $result[2]['id']);
        $this->assertEquals(10, $result[3]['id']);
    }

    public function testAddOrderBy()
    {
        $query = $this->createQuery();
        $query->addOrderBy('id', 'asc');

        $result = $query->fetchAll();

        $this->assertCount(4, $result);
        $this->assertEquals(2, $result[0]['id']);
        $this->assertEquals(10, $result[1]['id']);
        $this->assertEquals(20,  $result[2]['id']);
        $this->assertEquals(30, $result[3]['id']);
    }

}
