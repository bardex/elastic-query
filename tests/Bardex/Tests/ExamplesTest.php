<?php namespace Bardex\Tests;

use Bardex\Elastic\Client;
use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;

class ExamplesTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 2,
            'title' => 'Happy friends',
        ],
        [
            'id' => 20,
            'title' => 'title Alice record',
        ],
    ];


    public function testQuickStart()
    {
        $host  = getenv('ELASTIC_HOST');
        $index = getenv('ELASTIC_TEST_INDEX');
        $type  = static::$typeName;

        $client = Client::create($host);

        $query = $client->createSearchQuery();
        $results = $query->setIndex($index, $type)
              ->where('id')->equal(2)
              ->fetchAll();

        // count of fetched results
        $countResults = count($results);

        // count of total found results
        $totalFound = $results->getTotalFound();

        // iterate results
        foreach ($results as $result) {
        }

        // get first result (or null if empty)
        $first = $results->getFirst();

        // nothing found ?
        $isEmpty = $results->isEmpty();


        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(SearchQuery::class, $query);
        $this->assertInstanceOf(SearchResult::class, $results);
        $this->assertEquals(1, $countResults);
        $this->assertEquals(1, $totalFound);
        $this->assertNotEmpty($first);
        $this->assertFalse($isEmpty);
    }


}
