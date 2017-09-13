<?php namespace Bardex\Tests;

use Bardex\Elastic\SearchResult;

class ExecuteMultiQueryTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 20,
            'title' => '20 record',
        ],
        [
            'id' => 10,
            'title' => '10 record',
        ],
        [
            'id' => 30,
            'title' => '30 record',
        ]
    ];


    public function testMultiQuery()
    {
        $multi = $this->createMultyQuery();

        $first   = $this->createQuery()->where('id')->equal(10);
        $second  = $this->createQuery()->where('id')->equal(20);

        $multi->addQuery('first', $first);
        $multi->addQuery('second', $second);

        $client = $this->createClient();
        $results = $client->executeMultiQuery($multi);

        $this->assertInstanceOf(SearchResult::class, $results, 'instance_of');
        $this->assertInstanceOf(SearchResult::class, $results['first'], 'first_instance_of');
        $this->assertInstanceOf(SearchResult::class, $results['second'], 'second_instance_of');

        $this->assertCount(1, $results['first'], 'first_count');
        $this->assertCount(1, $results['second'], 'second_count');
    }
}
