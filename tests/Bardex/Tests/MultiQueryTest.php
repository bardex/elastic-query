<?php
namespace Bardex\Tests;

use \Bardex\Elastic\Query;

class MultiQueryTest extends AbstractTestCase
{
    protected static $typeName  = 'multi_1';
    protected static $typeName2 = 'multi_2';
    protected static $typeName3 = 'multi_3';

    protected static $testdata = [
        [
            'id' => 20,
            'title' => 'multi_1 one record',
        ],
        [
            'id' => 10,
            'title' => 'multi_1 two record',
        ]
    ];

    protected static $testdata2 = [
        [
            'id' => 2,
            'title' => 'multi_2 one record',
        ],
        [
            'id' => 1,
            'title' => 'multi_2 two record',
        ]
    ];

    protected static $testdata3 = [
        [
            'id' => 200,
            'title' => 'multi_3 one record',
        ],
        [
            'id' => 100,
            'title' => 'multi_3 two record',
        ]
    ];

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::setupTestData(static::$typeName, static::$testdata);
        self::setupTestData(static::$typeName2, static::$testdata2);
        self::setupTestData(static::$typeName3, static::$testdata3);
    }

    public function testMultiQuery()
    {
        $q1 = $this->createQuery();
       // $q1->whereGreater('id', 0);

        $q2 = $this->createQuery();
        $q2->setType(self::$typeName2);
      //  $q2->whereGreater('id', 0);

        $q3 = $this->createQuery();
        $q3->setType(self::$typeName3);
       // $q3->whereGreater('id', 0);

        $multi = new \Bardex\Elastic\MultiQuery(self::$client);

        $multi->addQuery($q1);
        $multi->addQuery($q2);
        $multi->addQuery($q3);

        $rows = $multi->fetchAll();

        print_r($rows);

    }
}