<?php

namespace Bardex\Tests;

use Bardex\Elastic\SearchQuery;
use Bardex\Elastic\SearchResult;


class SelectTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 2,
            'title' => 'Text title',
            'status' => 1,
            'rating' => 12
        ]
    ];

    public function testSelect()
    {
        $query = $this->createQuery();
        $query->select(['id', 'title']);
        $result = $query->fetchAll();
        $result = $result->getFirst();

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('title', $result);
    }

    public function testExclude()
    {
        $query = $this->createQuery();
        $query->exclude(['title', 'rating']);
        $result = $query->fetchAll();
        $result = $result->getFirst();

        $this->assertCount(2, $result);
        $this->assertArrayNotHasKey('title', $result);
        $this->assertArrayNotHasKey('rating', $result);
    }

}
