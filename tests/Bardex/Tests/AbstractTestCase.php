<?php

namespace Bardex\Tests;

use Bardex\Elastic\Query;


abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
    * @var \Elasticsearch\Client $client;
    */
    protected static $client;

    protected static $indexName;

    protected static $typeName;


    public static function setClient(\Elasticsearch\Client $client, $index, $type)
    {
        self::$client    = $client;
        self::$indexName = $index;
        self::$typeName  = $type;
    }


    protected function createQuery()
    {
        $query = new Query(static::$client);
        $query->setIndex(static::$indexName)
              ->setType(static::$typeName);

        return $query;
    }
}
