<?php

namespace Bardex\Tests;

use Bardex\Elastic\Client;
use Bardex\Elastic\SearchQuery;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
    * @var \Elasticsearch\Client $client;
    */
    protected static $client;
    protected static $indexName;
    protected static $typeName;
    protected static $testdata;

    public static function setClient(\Elasticsearch\Client $client, $index)
    {
        static::$client    = $client;
        static::$indexName = $index;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$typeName = str_replace('\\', '_', get_called_class());

        if ( ! empty(static::$testdata) ) {
            foreach (static::$testdata as $data) {
                $params = [
                    'index' => static::$indexName,
                    'type'  => static::$typeName,
                    'id'    => $data['id'],
                    'body'  => $data
                ];
                static::$client->index($params);
            }
            static::$client->indices()->refresh(['index' => static::$indexName]);
        }
    }


    protected function createQuery()
    {
        $query = $this->createClient()->createSearchQuery();
        $query->setIndex(static::$indexName)
              ->setType(static::$typeName);

        return $query;
    }

    protected function createMultyQuery()
    {
        $query = $this->createClient()->createMultiQuery();
        return $query;
    }

    protected function createClient()
    {
        return new Client(static::$client);
    }
}
