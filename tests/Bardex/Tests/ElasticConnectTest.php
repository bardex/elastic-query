<?php

namespace Bardex\Tests;

class ElasticConnectTest extends AbstractTestCase
{

    public function testConnection()
    {
        $this->assertTrue( $this->client->ping() );
    }


}
