<?php

namespace Bardex\Tests;

use Bardex\Elastic\SearchResult;


class SearchResultTest extends AbstractTestCase
{
    public function testSearchResult()
    {
        $testResults = [
            ['id' => 10],
            ['id' => 20],
        ];

        $limit = count($testResults);
        $total = $limit * 2;

        $result = new SearchResult($testResults, $total);

        // not empty
        $this->assertFalse($result->isEmpty());

        // test countable
        $this->assertCount($limit, $result);

        // test total count
        $this->assertEquals($total, $result->getTotalFound());

        // test get first
        $this->assertEquals($testResults[0], $result->getFirst());

        // test array access
        for ($i=0; $i < $limit; ++$i) {
            $this->assertEquals($testResults[$i], $result[$i]);
        }

        // test iterator
        foreach ($result as $i => $item) {
            $this->assertEquals($testResults[$i], $item);
        }

        // get all results
        $this->assertEquals($testResults, $result->getResults());

        // isset, unset
        $this->assertTrue(isset($result[0]));
        unset($result[0]);
        $this->assertFalse(isset($result[0]));

        // set
        $newSet = [10,20,30];
        $result[1] = $newSet;

        $this->assertEquals($newSet, $result[1]);

    }

    public function testEmpty()
    {
        $result = new SearchResult(null, 0);

        $this->assertTrue($result->isEmpty());
        $this->assertEmpty($result->getFirst());
    }

}
