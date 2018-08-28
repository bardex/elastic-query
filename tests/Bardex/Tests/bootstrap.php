<?php

require __DIR__ . '/../../../vendor/autoload.php';

// create elastic client
$builder = \Elasticsearch\ClientBuilder::create();
$builder->setHosts([getenv('ELASTIC_HOST')]);
$client = $builder->build();

\Bardex\Tests\AbstractTestCase::setClient($client);
