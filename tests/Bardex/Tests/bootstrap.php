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

if ( $client->indices()->exists(['index' => $index]) ) {
    $client->indices()->delete(['index' => $index]);
}

// create test index
$params = [
    'index' => $index,
];

$client->indices()->create($params);


print_r( $client->info() );

$testdata = require __DIR__ . '/testdata.php';

foreach ($testdata as $data) {
    $params = [
        'index' => $index,
        'type'  => $type,
        'id'    => $data['id'],
        'body'  => $data
    ];
}

sleep(5);

$params = [
        'index' => $index,
        'type'  => $type,
        'id'    => 1,
    ];

var_dump( $client->get($params) );

