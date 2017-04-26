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
        $validData = self::getValidTestData();

        $query  = $this->createQuery();
        $query->where('id', $validData['id']);
        $result = $query->fetchOne();

        $this->assertArraySubset($validData, $result);
        $this->assertEquals(1, $query->getTotalResults());
    }


}
