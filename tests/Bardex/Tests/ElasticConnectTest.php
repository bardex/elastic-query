<?php

namespace Bardex\Tests;

class ElasticConnectTest extends AbstractTestCase
{

    public function testConnection()
    {
        $client = self::$client;
        $this->assertInstanceOf(\Elasticsearch\Client::class, $client);
        $this->assertTrue( $client->ping() );
    }


}
