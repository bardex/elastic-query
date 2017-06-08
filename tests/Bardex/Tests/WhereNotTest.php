<?php

namespace Bardex\Tests;

use Bardex\Elastic\SearchResult;

class WhereNotTest extends AbstractTestCase
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
            'channels' => [1,2,3],
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
        ['name' => 'not equal',   'method' => 'not',        'field' => 'id',  'params' => [10], 'validCount' => 2, 'validId' => [2,20]],
        ['name' => 'not in',      'method' => 'notIn',      'field' => 'id',  'params' => [[10,20]], 'validCount' => 1, 'validId' => [2]],
        ['name' => 'not between', 'method' => 'notBetween', 'field' => 'id',  'params' => [10,20], 'validCount' => 1, 'validId' => [2]],
        ['name' => 'not between date', 'method' => 'notBetween', 'field' => 'publicDate', 'params' => ['2016-12-31T23:00:00+03:00','2016-12-31T23:00:00+03:00', 'date_time_no_millis'], 'validCount' => 2, 'validId' => [2,20]],
        ['name' => 'not match',   'method' => 'notMatch',   'field' => 'title',  'params' => ['alice'], 'validCount' => 2, 'validId' => [2,10]],
        ['name' => 'not match multi', 'method' => 'notMatch', 'field' => ['title','anons'], 'params' => ['alice'], 'validCount' => 1, 'validId' => [2]],
    ];

    public function testWhereMethods()
    {
        foreach ($this->mytests as $test) {
            $query = $this->createQuery();
            $whereHelper = $query->where($test['field']);
            call_user_func_array([$whereHelper, $test['method']], $test['params']);
            $results = $query->fetchAll();

            $this->assertInstanceOf(SearchResult::class, $results);

            $this->assertCount($test['validCount'], $results, "test method: ".$test['name']);
            $this->assertEquals($test['validCount'], $results->getTotalCount(), "test method: ".$test['name']);
            foreach ($results as $result) {
                $this->assertContains($result['id'], $test['validId'], "test method: ".$test['name']);
            }

            $this->assertEquals(count($test['validId']), $test['validCount'], "test method: ".$test['name']);
        }
    }




}
