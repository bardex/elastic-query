<?php namespace Bardex\Tests;

use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;

class ScoreTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 2,
            'title' => 'Happy friends',
            'channels' => [75],
            'publicDate' => '2002-01-01T00:00:00+03:00',
        ],
        [
            'id' => 20,
            'title' => 'title Alice record',
            'channels' => [1, 2, 3],
            'publicDate' => '2017-01-01T00:00:00+03:00',
        ],
        [
            'id' => 10,
            'title' => 'title Bob record',
            'anons' => 'Bob is Alice friend',
            'channels' => [],
            'publicDate' => '2016-12-31T23:00:00+03:00',
        ]
    ];



    public function testScoreExists()
    {
        /** @var SearchQuery $query */
        $query = $this->createQuery();
        /** @var SearchResult $results */
        $results = $query->where('title')->match('friends')->fetchAll();
        $item = $results->getFirst();
        $this->assertArrayHasKey('__score', $item);
    }
}
