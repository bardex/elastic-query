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

    protected static $testdata;

    public static function setClient(\Elasticsearch\Client $client, $index, $type)
    {
        self::$client    = $client;
        self::$indexName = $index;
        self::$typeName  = $type;
    }

    public static function setTestData(array $testdata)
    {
        self::$testdata = $testdata;
    }

    public static function getTestData()
    {
        return self::$testdata;
    }

    public static function getValidTestData()
    {
        foreach (self::$testdata as $data) {
            if ($data['_label'] == 'VALID') {
                return $data;
            }
        }
        return null;
    }

    public static function getInvalidTestData()
    {
        foreach (self::$testdata as $data) {
            if ($data['_label'] == 'INVALID') {
                return $data;
            }
        }
        return null;
    }

    protected function createQuery()
    {
        $query = new Query(static::$client);
        $query->setIndex(static::$indexName)
              ->setType(static::$typeName);

        return $query;
    }
}
