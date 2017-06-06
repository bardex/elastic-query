<?php

namespace Bardex\Tests;

use Bardex\Elastic\MultiQuery;
use \Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;


class MultiQueryTest extends AbstractTestCase
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
        $firstQuery  = $this->createQuery()->where('id')->equal(10);
        $secondQuery = $this->createQuery()->where('id')->in([20,30]);

        $multiQuery = new MultiQuery(static::$client);
        $multiQuery->addQuery('first', $firstQuery);
        $multiQuery->addQuery('second', $secondQuery);
        $results = $multiQuery->fetchAll();

        $this->assertInstanceOf(SearchResult::class, $results, 'instance_of');
        $this->assertInstanceOf(SearchResult::class, $results['first'], 'first_instance_of');
        $this->assertInstanceOf(SearchResult::class, $results['second'], 'second_instance_of');

        $this->assertCount(1, $results['first'], 'first_count');
        $this->assertCount(2, $results['second'], 'second_count');

        $this->assertEquals(1, $results['first']->getTotalCount(), 'first_total_count');
        $this->assertEquals(2, $results['second']->getTotalCount(), 'second_total_count');
    }
}
