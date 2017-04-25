<?php

namespace Bardex\Tests;

use \Bardex\Elastic\Query;

class QueryTest extends AbstractTestCase
{
    public static function setUpBeforeClass()
    {
        static::$typeName = 'testselect';

        $params = [
            'index' => self::$indexName,
            'type'  => self::$typeName,
            'id'    => 1,
            'body'  => [
                'id'=> 1,
                'title' => 'test',
                'anons' => 'test anons',
                'topic' => [
                    'id' => 2,
                    'title' => 'topic',
                    'anons' => 'topic anons'
                ]
            ]
        ];

        static::$client->index($params);
    }


    public function testOneSelect()
    {
        $select = ['id'];
        $result = $this->createQuery()
                    ->select($select)
                    ->where('id', 1)
                    ->fetchOne();

        $resultKeys = array_keys($result);

        sort($select);
        sort($resultKeys);

        $this->assertEquals($select, $resultKeys);
    }


}
