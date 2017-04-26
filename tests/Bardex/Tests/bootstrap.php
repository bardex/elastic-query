<?php

require __DIR__ . '/../../../vendor/autoload.php';

// create elastic client
$host  = getenv('ELASTIC_HOST');
$index = getenv('ELASTIC_TEST_INDEX');

$builder = \Elasticsearch\ClientBuilder::create();
$builder->setHosts([$host]);
$client = $builder->build();

\Bardex\Tests\AbstractTestCase::setClient($client, $index);

// drop index if exists
if ($client->indices()->exists(['index' => $index])) {
    $client->indices()->delete(['index' => $index]);
}

// create test index
$params = [
    'index' => $index,
    'body'  => [
        'settings' => [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
        ]
    ]
];

$client->indices()->create($params);
