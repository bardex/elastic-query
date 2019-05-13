<?php namespace Bardex\Tests;

use Bardex\Elastic\Query;
use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;

class ScoreTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 1,
            'title' => 'Happy friends',
            'status' => 1,
        ],
        [
            'id' => 2,
            'title' => 'Alice',
            'status' => 1,
        ],
        [
            'id' => 3,
            'title' => 'Bob',
            'status' => 1,
        ]
    ];


    public function testMinScore()
    {
        /** @var SearchQuery $query */
        $query = $this->createQuery();

        $query->where('title', Query::CONTEXT_SHOULD)->match('friends')
              ->where('status', Query::CONTEXT_FILTER)->equal(1)
              ->minScore(0.1)
              ->setOrderBy('_score', 'desc');

        $results = $query->fetchAll();

        $this->assertCount(1, $results);

        $item = $results->getFirst();

        $this->assertEquals(1, $item['id']);
    }
}
