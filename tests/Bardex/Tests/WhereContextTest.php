<?php namespace Bardex\Tests;

use Bardex\Elastic\Query;

class WhereContextTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 1,
            'title' => 'title Happy friends',
            'channels' => [1],
            'status' => 0,
        ],
        [
            'id' => 2,
            'title' => 'title Alice record',
            'channels' => [1],
            'status' => 1,
        ],
        [
            'id' => 3,
            'title' => 'title Bob record',
            'anons' => 'Bob is Alice friend',
            'channels' => [1, 2, 3],
            'status' => 0,
        ]
    ];


    public function testFilterContext()
    {
        $query = $this->createQuery();
        $query->where('status', Query::CONTEXT_FILTER)->equal(1)
              ->where('channels', Query::CONTEXT_FILTER)->in([1,2,3]);

        $results = $query->fetchAll();

        $this->assertEquals(1, $results->getTotalFound());
        $this->assertEquals(2, $results[0]['id']);
    }

    public function testMustContext()
    {
        $query = $this->createQuery();
        $query->where('title', Query::CONTEXT_MUST)->match('title')
            ->where('channels', Query::CONTEXT_MUST)->in([2]);

        $results = $query->fetchAll();

        $this->assertEquals(1, $results->getTotalFound());
        $this->assertEquals(3, $results[0]['id']);
    }

    public function testShouldContext()
    {
        $query = $this->createQuery();
        $query->where('title', Query::CONTEXT_SHOULD)->match('title alice')
            ->where('channels', Query::CONTEXT_SHOULD)->in([2,3])
            ->setOrderBy(Query::ORDER_BY_SCORE, 'desc') //for should context order work
            ->addOrderBy('id', 'asc');

        $results = $query->fetchAll();

        $this->assertEquals(3, $results->getTotalFound());

        $this->assertEquals(3, $results[0]['id']);
        $this->assertEquals(2, $results[1]['id']);
        $this->assertEquals(1, $results[2]['id']);
    }

    public function testFilterAndShouldContext()
    {
        $query = $this->createQuery();
        $query->where('status', Query::CONTEXT_FILTER)->equal(0) // filtered by status
            ->where('title', Query::CONTEXT_SHOULD)->match('title') // title OR channels must match
            ->where('channels', Query::CONTEXT_SHOULD)->equal(1)
            ->addOrderBy('id', 'asc');

        $results = $query->fetchAll();

        $this->assertEquals(2, $results->getTotalFound());
        $this->assertEquals(1, $results[0]['id']);
        $this->assertEquals(3, $results[1]['id']);
    }
}
