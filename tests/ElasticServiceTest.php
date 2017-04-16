<?php

class ElasticServiceTest extends PHPUnit_Framework_TestCase
{

    /**
    * @var \Elasticsearch\Client $client;
    */
    protected $client;


    public function setUp()
    {
        $this->client = \Elasticsearch\ClientBuilder::create()->build();
    }

    public function testConnection()
    {
        $this->assertTrue( $this->client->ping() );
    }


}
