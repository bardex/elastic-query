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
        $this->assertEquals($total, $result->getTotalCount());

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
    }

    public function testEmpty()
    {
        $result = new SearchResult(null, 0);

        $this->assertTrue($result->isEmpty());
        $this->assertEmpty($result->getFirst());
    }

}
