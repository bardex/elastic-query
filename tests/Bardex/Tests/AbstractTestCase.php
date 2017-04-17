<?php

namespace Bardex\Tests;

use Elasticsearch\ClientBuilder;


abstract class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
    * @var \Elasticsearch\Client $client;
    */
    protected $client;

    public function setUp()
    {
        $builder = ClientBuilder::create();

        if ($host = getenv('ELASTIC_HOST')) {
            $builder->setHosts([$host]);
        }

        $this->client = $builder->build();
    }

}
