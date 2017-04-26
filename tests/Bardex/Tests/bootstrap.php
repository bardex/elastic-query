<?php

require __DIR__ . '/../../../vendor/autoload.php';

// create elastic client
$host  = getenv('ELASTIC_HOST');
$index = getenv('ELASTIC_TEST_INDEX');
$type  = getenv('ELASTIC_TEST_TYPE');

$builder = \Elasticsearch\ClientBuilder::create();
$builder->setHosts([$host]);
$client = $builder->build();

\Bardex\Tests\AbstractTestCase::setClient($client, $index, $type);

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

$testdata = require __DIR__ . '/testdata.php';

foreach ($testdata as $data) {
    $params = [
        'index' => $index,
        'type'  => $type,
        'id'    => $data['id'],
        'body'  => $data
    ];

    $client->index($params);
}

\Bardex\Tests\AbstractTestCase::setTestData($testdata);

// sleep for elastic search synchronize data
sleep(1);
