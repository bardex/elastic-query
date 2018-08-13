<?php namespace Bardex\Tests;

use Bardex\Elastic\Query;
use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;

class WhereTest extends AbstractTestCase
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

    protected $mytests = [
        [
            'name' => 'equal',
            'method' => 'equal',
            'field' => 'id',
            'params' => [20],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'in',
            'method' => 'in',
            'field' => 'channels',
            'params' => [[2]],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'match',
            'method' => 'match',
            'field' => 'title',
            'params' => ['alice'],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'matchAnd',
            'method' => 'match',
            'field' => 'title',
            'params' => ['alice record', 'and'],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'matchOr',
            'method' => 'match',
            'field' => 'title',
            'params' => ['happy alice'],
            'validCount' => 2,
            'validId' => [2, 20]
        ],
        [
            'name' => 'exists',
            'method' => 'exists',
            'field' => 'channels',
            'params' => [],
            'validCount' => 2,
            'validId' => [20, 2]
        ],
        [
            'name' => 'between',
            'method' => 'between',
            'field' => 'id',
            'params' => [15, 25],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'greater',
            'method' => 'greater',
            'field' => 'id',
            'params' => [15],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'greaterEqual',
            'method' => 'greaterOrEqual',
            'field' => 'id',
            'params' => [10],
            'validCount' => 2,
            'validId' => [10, 20]
        ],
        ['name' => 'less', 'method' => 'less', 'field' => 'id', 'params' => [10], 'validCount' => 1, 'validId' => [2]],
        [
            'name' => 'lessEqual',
            'method' => 'lessOrEqual',
            'field' => 'id',
            'params' => [10],
            'validCount' => 2,
            'validId' => [10, 2]
        ],
        [
            'name' => 'between date',
            'method' => 'between',
            'field' => 'publicDate',
            'params' => ['2016-12-31T23:00:00+03:00', '2016-12-31T23:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 1,
            'validId' => [10]
        ],
        [
            'name' => 'greater date',
            'method' => 'greater',
            'field' => 'publicDate',
            'params' => ['2016-12-31T23:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'greaterEqual date',
            'method' => 'greaterOrEqual',
            'field' => 'publicDate',
            'params' => ['2016-12-31T23:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 2,
            'validId' => [10, 20]
        ],
        [
            'name' => 'less date',
            'method' => 'less',
            'field' => 'publicDate',
            'params' => ['2016-12-31T23:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 1,
            'validId' => [2]
        ],
        [
            'name' => 'lessEqual date',
            'method' => 'lessOrEqual',
            'field' => 'publicDate',
            'params' => ['2016-12-31T23:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 2,
            'validId' => [10, 2]
        ],
        [
            'name' => 'match multi fields',
            'method' => 'match',
            'field' => ['title', 'anons'],
            'params' => ['alice'],
            'validCount' => 2,
            'validId' => [20, 10]
        ],
    ];

    public function testInstanceOf()
    {
        $query = $this->createQuery();
        $this->assertInstanceOf(SearchQuery::class, $query);
    }


    public function testWhereMethods($context = Query::CONTEXT_DEFAULT)
    {
        foreach ($this->mytests as $test) {
            $query = $this->createQuery();
            $whereHelper = $query->where($test['field'], $context);
            call_user_func_array([$whereHelper, $test['method']], $test['params']);
            $results = $query->fetchAll();

            $this->assertInstanceOf(SearchResult::class, $results);

            $methodName = "test method: {$test['name']} context:{$context}";

            $this->assertCount($test['validCount'], $results, $methodName);
            $this->assertEquals($test['validCount'], $results->getTotalFound(), $methodName);
            foreach ($results as $result) {
                $this->assertContains($result['id'], $test['validId'], $methodName);
            }
            $this->assertEquals(count($test['validId']), $test['validCount'], $methodName);
        }
    }

    public function testContextCompatibility()
    {
        $context = [Query::CONTEXT_FILTER, Query::CONTEXT_SHOULD, Query::CONTEXT_MUST];
        foreach ($context as $c) {
            $this->testWhereMethods($c);
        }
    }
}
