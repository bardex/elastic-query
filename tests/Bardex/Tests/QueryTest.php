<?php

namespace Bardex\Tests;

use \Bardex\Elastic\Query;

class QueryTest extends AbstractTestCase
{

    public function testInstanceOf()
    {
        $query = $this->createQuery();
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testWhere()
    {
        $query  = $this->createQuery();
        $query->where('id', 1);
        $result = $query->fetchOne();
        print_r($query->getQuery());

        $this->assertInternalType('array', $result);
        $this->assertEquals(1, $query->getTotalResults());
    }

    public function testFetchRaw()
    {
        $query  = $this->createQuery();
        $query->where('id', 1);
        $result = $query->fetchRaw();
        $this->assertInternalType('array', $result);
    }

}
