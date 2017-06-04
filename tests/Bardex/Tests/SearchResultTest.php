<?php

namespace Bardex\Tests;

use Bardex\Elastic\SearchResult;


class SearchResultTest extends AbstractTestCase
{
    protected static $testdata = [
        ['id' => 10],
        ['id' => 20],
        ['id' => 30]
    ];

    public function testSearchResult()
    {
        $limit = 2;
        $q = $this->createQuery();
        $q->setOrderBy('id', 'asc');
        $q->limit($limit);

        $result = $q->fetchAll();

        // test instance of
        $this->assertInstanceOf(SearchResult::class, $result);

        // test countable
        $this->assertCount($limit, $result);

        // test total count
        $this->assertEquals(count(self::$testdata), $result->getTotalCount());

        // test get first
        $this->assertEquals(self::$testdata[0], $result->getFirst());

        // test array access
        for ($i=0; $i<$limit; ++$i) {
            $this->assertEquals(self::$testdata[$i], $result[$i]);
        }

        // test iterator
        foreach ($result as $i => $item) {
            $this->assertEquals(self::$testdata[$i], $item);
        }
    }

}
