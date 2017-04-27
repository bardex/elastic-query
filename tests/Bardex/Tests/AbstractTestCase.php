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

    public static function setClient(\Elasticsearch\Client $client, $index)
    {
        static::$client    = $client;
        static::$indexName = $index;
    }

    public static function setupTestData($type, $testdata)
    {
        foreach ($testdata as $data) {
            $params = [
                'index' => static::$indexName,
                'type'  => $type,
                'id'    => $data['id'],
                'body'  => $data
            ];
            static::$client->index($params);
        }
        static::$client->indices()->refresh(['index' => static::$indexName]);
    }

    protected function createQuery()
    {
        $query = new Query(static::$client);
        $query->setIndex(static::$indexName)
              ->setType(static::$typeName);

        return $query;
    }
}
