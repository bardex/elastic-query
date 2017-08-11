<?php namespace Bardex\Tests;

use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;

class WhereRegExpTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 2,
            'title' => 'Happy',
        ],
        [
            'id' => 20,
            'title' => 'Nation',
        ],
        [
            'id' => 10,
            'title' => 'Song',
        ]
    ];

    protected $mytests = [
        [
            'name' => 'wildcard',
            'method' => 'wildcard',
            'field'  => 'title',
            'params' => ['Nat*'],
            'validCount' => 1,
            'validId' => [20]
        ],
        [
            'name' => 'regexp',
            'method' => 'regexp',
            'field'  => 'title',
            'params' => ['^Hap.*'],
            'validCount' => 1,
            'validId' => [2]
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

            $this->assertCount($test['validCount'], $results, "test method: " . $test['name']);
            $this->assertEquals($test['validCount'], $results->getTotalFound(), "test method: " . $test['name']);
            foreach ($results as $result) {
                $this->assertContains($result['id'], $test['validId'], "test method: " . $test['name']);
            }

            $this->assertEquals(count($test['validId']), $test['validCount'], "test method: " . $test['name']);
        }
    }
}
