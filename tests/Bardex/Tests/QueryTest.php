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

        $params = [
            'index' => self::$indexName,
            'type'  => self::$typeName,
            'id'    => 1,
        ];

        var_dump(self::$client->get($params) );



        $query  = $this->createQuery();
        $query->where('id', 1);
        $result = $query->fetchAll();
        print_r($query->getQuery());
        var_dump($result);

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
