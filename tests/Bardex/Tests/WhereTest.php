<?php

namespace Bardex\Tests;

use \Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;


class WhereTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 20,
            'title' => 'title Alice record',
            'channels' => [1,2,3],
            'publicDate' => '2017-01-01T00:00:00+03:00',
            'tags' => [
                [
                    'id' => 15,
                    'title' => 'pop'
                ]
            ]
        ],
        [
            'id' => 10,
            'title' => 'title Bob record',
            'channels' => [1],
            'publicDate' => '2016-12-31T23:00:00+03:00',
        ]
    ];

    protected $mytests = [
        [
            'name'       => 'whereEqual',
            'method'     => 'equal',
            'field'      => 'id',
            'params'     => [20],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereInOne',
            'method'     => 'in',
            'field'      => 'channels',
            'params'     => [[2]],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereInNothing',
            'method'     => 'in',
            'field'      => 'channels',
            'params'     => [[5,6]],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereInBoth',
            'method'     => 'in',
            'field'      => 'channels',
            'params'     => [[1,2]],
            'validCount' => 2,
            'validId'    => [20,10]
        ],
        [
            'name'       => 'whereMatchOne',
            'method'     => 'match',
            'field'      => 'title',
            'params'     => ['alice'],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereMatchBoth',
            'method'     => 'match',
            'field'      => 'title',
            'params'     => ['record'],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereMatchNothing',
            'method'     => 'match',
            'field'      => 'title',
            'params'     => ['zitta'],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereBetweenOne',
            'method'     => 'between',
            'field'      => 'id',
            'params'     => [15, 25],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereBetweenBoth',
            'method'     => 'between',
            'field'      => 'id',
            'params'     => [10, 20],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereBetweenNothing',
            'method'     => 'between',
            'field'      => 'id',
            'params'     => [50, 60],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereBetweenDateOne',
            'method'     => 'between',
            'field'      => 'publicDate',
            'params'     => ['2017-01-01T00:00:00+03:00', '2017-01-01T00:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereBetweenDateTwo',
            'method'     => 'between',
            'field'      => 'publicDate',
            'params'     => ['2016-12-31T00:00:00+03:00', '2017-01-01T00:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 2,
            'validId'    => [20,10]
        ],
        [
            'name'       => 'whereBetweenDateNothing',
            'method'     => 'between',
            'field'      => 'publicDate',
            'params'     => ['2016-12-31T00:00:00+03:00', '2016-12-31T12:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereGreaterOne',
            'method'     => 'greater',
            'field'      => 'id',
            'params'     => [15],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereGreaterBoth',
            'method'     => 'greater',
            'field'      => 'id',
            'params'     => [0],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereGreaterNothing',
            'method'     => 'greater',
            'field'      => 'id',
            'params'     => [20],
            'validCount' => 0,
            'validId'    => []
        ],
    ];

    public function testInstanceOf()
    {
        $query = $this->createQuery();
        $this->assertInstanceOf(SearchQuery::class, $query);
    }

    public function testWhereMethods()
    {
        foreach ($this->mytests as $test) {
            $query = $this->createQuery();
            $whereHelper = $query->where($test['field']);
            call_user_func_array([$whereHelper, $test['method']], $test['params']);
            $results = $query->fetchAll();

            $this->assertInstanceOf(SearchResult::class, $results);

            $this->assertCount($test['validCount'], $results, "test: ".$test['name']);
            $this->assertEquals($test['validCount'], $results->getTotalCount(), "test: ".$test['name']);
            foreach ($results as $result) {
                $this->assertContains($result['id'], $test['validId'], "test: ".$test['name']);
            }

            $this->assertEquals(count($test['validId']), $test['validCount'], "test: ".$test['name']);
        }
    }




}
