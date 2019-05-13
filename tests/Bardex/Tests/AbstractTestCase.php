<?php namespace Bardex\Tests;

use Bardex\Elastic\Client;

abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Elasticsearch\Client $client ;
     */
    protected static $client;
    protected static $indexName;
    protected static $typeName;
    protected static $testdata;

    public static function setClient(\Elasticsearch\Client $client)
    {
        static::$client = $client;
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$typeName  = strtolower(str_replace('\\', '_', get_called_class()));
        static::$indexName = strtolower(str_replace('\\', '_', get_called_class()));

        $client = static::$client;
        $index  = static::$indexName;
        $type   = static::$typeName;

        // drop index if exists
        if ($client->indices()->exists(['index' => $index])) {
            $client->indices()->delete(['index' => $index]);
        }

        // create test index
        $client->indices()->create([
            'index' => $index,
            'body'  => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ]
            ]
        ]);

        if (!empty(static::$testdata)) {
            foreach (static::$testdata as $data) {
                $client->index([
                    'index' => $index,
                    'type'  => $type,
                    'id'    => $data['id'],
                    'body'  => $data
                ]);
            }
            $client->indices()->refresh(['index' => $index]);
        }
    }

    /**
     * @return \Bardex\Elastic\SearchQuery
     */
    protected function createQuery()
    {
        $query = $this->createClient()->createSearchQuery();
        $query->setIndex(static::$indexName, static::$typeName);

        return $query;
    }

    /**
     * @return \Bardex\Elastic\MultiQuery
     */
    protected function createMultyQuery()
    {
        $query = $this->createClient()->createMultiQuery();
        return $query;
    }

    /**
     * @return Client
     */
    protected function createClient()
    {
        return new Client(static::$client);
    }
}
