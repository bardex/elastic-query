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

        $results = $this->createMultyQuery()
            ->addQuery($firstQuery)
            ->addQuery($secondQuery)
            ->fetchAll();

        $this->assertInstanceOf(SearchResult::class, $results, 'instance_of');
        $this->assertInstanceOf(SearchResult::class, $results[0], 'first_instance_of');
        $this->assertInstanceOf(SearchResult::class, $results[1], 'second_instance_of');

        $this->assertCount(1, $results[0], 'first_count');
        $this->assertCount(2, $results[1], 'second_count');

        $this->assertEquals(1, $results[0]->getTotalCount(), 'first_total_count');
        $this->assertEquals(2, $results[1]->getTotalCount(), 'second_total_count');
    }
}
