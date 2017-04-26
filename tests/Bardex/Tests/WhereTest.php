<?php

namespace Bardex\Tests;

use \Bardex\Elastic\Query;

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
            'name'       => 'where',
            'method'     => 'where',
            'params'     => ['id', 20],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereInOne',
            'method'     => 'whereIn',
            'params'     => ['channels', [2]],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereInNothing',
            'method'     => 'whereIn',
            'params'     => ['channels', [5,6]],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereInBoth',
            'method'     => 'whereIn',
            'params'     => ['channels', [1,2]],
            'validCount' => 2,
            'validId'    => [20,10]
        ],
        [
            'name'       => 'whereMatchOne',
            'method'     => 'whereMatch',
            'params'     => ['title', 'alice'],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereMatchBoth',
            'method'     => 'whereMatch',
            'params'     => ['title', 'record'],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereMatchNothing',
            'method'     => 'whereMatch',
            'params'     => ['title', 'zitta'],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereBetweenOne',
            'method'     => 'whereBetween',
            'params'     => ['id', 15, 25],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereBetweenBoth',
            'method'     => 'whereBetween',
            'params'     => ['id', 10, 20],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereBetweenNothing',
            'method'     => 'whereBetween',
            'params'     => ['id', 50, 60],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereBetweenDateOne',
            'method'     => 'whereBetween',
            'params'     => ['publicDate', '2017-01-01T00:00:00+03:00', '2017-01-01T00:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereBetweenDateTwo',
            'method'     => 'whereBetween',
            'params'     => ['publicDate', '2016-12-31T00:00:00+03:00', '2017-01-01T00:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 2,
            'validId'    => [20,10]
        ],
        [
            'name'       => 'whereBetweenDateNothing',
            'method'     => 'whereBetween',
            'params'     => ['publicDate', '2016-12-31T00:00:00+03:00', '2016-12-31T12:00:00+03:00', 'date_time_no_millis'],
            'validCount' => 0,
            'validId'    => []
        ],
        [
            'name'       => 'whereGreaterOne',
            'method'     => 'whereGreater',
            'params'     => ['id', 15],
            'validCount' => 1,
            'validId'    => [20]
        ],
        [
            'name'       => 'whereGreaterBoth',
            'method'     => 'whereGreater',
            'params'     => ['id', 0],
            'validCount' => 2,
            'validId'    => [20, 10]
        ],
        [
            'name'       => 'whereGreaterNothing',
            'method'     => 'whereGreater',
            'params'     => ['id', 20],
            'validCount' => 0,
            'validId'    => []
        ],
    ];

    public function testInstanceOf()
    {
        $query = $this->createQuery();
        $this->assertInstanceOf(Query::class, $query);
    }

    public function testWhereMethods()
    {
        foreach ($this->mytests as $test) {
            $query = $this->createQuery();
            call_user_func_array([$query, $test['method']], $test['params']);
            $results = $query->fetchAll();

            $this->assertCount($test['validCount'], $results, "test: ".$test['name']);
            $this->assertEquals($test['validCount'], $query->getTotalResults(), "test: ".$test['name']);
            foreach ($results as $result) {
                $this->assertContains($result['id'], $test['validId'], "test: ".$test['name']);
            }

            $this->assertEquals(count($test['validId']), $test['validCount'], "test: ".$test['name']);
        }
    }




}
