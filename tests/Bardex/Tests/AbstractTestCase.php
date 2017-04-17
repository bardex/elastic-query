<?php

namespace Bardex\Tests;

use Bardex\Elastic\Query;
use Elasticsearch\ClientBuilder;


abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
    * @var \Elasticsearch\Client $client;
    */
    protected static $client;

    protected static $indexName = 'bardex_elastic_query_autotest';

    protected static $typeName  = 'bardex_elastic_query_autotest';

    protected static $testData = [
        [
            'id' => 1,
            'title' => 'first test record',
            'anons' => 'anons for first test record',
            'channels' => [10,20,30],
            'rating'   => 5,
            'publicDate' => '2017-01-01T00:00:00+03:00',
            'tags' => [
                [
                    'id' => 15,
                    'title' => 'pop'
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'second test record',
            'anons' => 'anons for second test record',
            'channels' => [10],
            'rating'   => 1,
            'publicDate' => '2016-01-01T00:00:00+03:00',
        ],
        [
            'id' => 3,
            'title' => 'third test record',
            'anons' => 'anons for third test record',
            'rating'   => 3,
            'publicDate' => '2018-01-01T00:00:00+03:00',
            'tags' => [
                [
                    'id' => 10,
                    'title' => 'rock'
                ]
            ]
        ],
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $builder = ClientBuilder::create();

        if ($host = getenv('ELASTIC_HOST')) {
            $builder->setHosts([$host]);
        }

        static::$client = $builder->build();

        static::createTestIndex();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::dropTestIndex();
    }


    protected static function createTestIndex()
    {
        $client = static::$client;

        $params = [
            'index' => static::$indexName
        ];

        if (!$client->indices()->exists($params)) {
            $params = [
                'index' => static::$indexName,
                'body'  => [
                    'settings' => [
                        'number_of_shards'   => 1,
                        'number_of_replicas' => 1
                    ]
                ]
            ];
            $client->indices()->create($params);

            static::createTestData();
        }
    }

    protected static function createTestData()
    {
        $client = static::$client;

        foreach (static::$testData as $data) {
            $params = [
                'index' => static::$indexName,
                'type'  => static::$typeName,
                'id'    => $data['id'],
                'body'  => $data
            ];

            $client->index($params);
        }
    }

    protected static function dropTestIndex()
    {
        $client = static::$client;

        $params = [
            'index' => static::$indexName
        ];

        if ($client->indices()->exists($params)) {
            $client->indices()->delete($params);
        }
    }

    protected function createQuery()
    {
        $query = new Query(static::$client);
        $query->setIndex(static::$indexName)
              ->setType(static::$typeName);

        return $query;
    }
}
