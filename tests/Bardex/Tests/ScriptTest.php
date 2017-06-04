<?php

namespace Bardex\Tests;

use \Bardex\Elastic\SearchQuery;
use \Bardex\Elastic\Script;

class ScriptTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 20,
            'title' => 'title Alice record',
            'channels' => [1,2,3],
            'publicDate' => '2017-01-01T00:00:00+03:00',
        ],
        [
            'id' => 10,
            'title' => 'title Bob record',
            'channels' => [1],
            'publicDate' => '2016-12-31T23:00:00+03:00',
        ]
    ];

    public function testScriptCompile()
    {
        $lang = 'groovy';
        $lineSeparator = " ";

        $lines = [];
        $lines[] = 'def A = 10;';
        $lines[] = 'def B = 20;';
        $lines[] = 'return params.power * (A + B);';

        $params = [];
        $params['power'] = 2;

        $script = new Script($lang);
        $script->setLineSeparator($lineSeparator);

        foreach ($lines as $line) {
            $script->addLine($line);
        }

        foreach ($params as $paramName => $paramValue) {
            $script->addParam($paramName, $paramValue);
        }

        $this->assertEquals($lang, $script->getLanguage());
        $this->assertEquals($params, $script->getParams());
        $this->assertEquals(implode($lineSeparator, $lines), $script->getBody());
    }


    public function testScriptFields()
    {
        $script = new Script();
        $script->addLine('def a = 100;');
        $script->addLine('return a;');

        $query = $this->createQuery();
        $query->addScriptField('test', $script);

        $queryBody = $query->getQuery();
        $this->assertTrue(isset($queryBody['body']['script_fields']['test']));

        $row = $query->fetchAll()->getFirst();
        $this->assertEquals(100, $row['test']);
    }

    public function testScriptFieldsWithParams()
    {
        $script = new Script();
        $script->addLine('return 10 * params.power;');
        $script->addParam('power', 2);

        $query = $this->createQuery();
        $query->addScriptField('test', $script);

        $row = $query->fetchAll()->getFirst();
        $this->assertEquals(20, $row['test']);
    }

    public function testWhereScript()
    {
        $script = new Script();
        $script->addLine("return doc['id'].value == params.test");
        $script->addParam('test', 10);

        $query = $this->createQuery();
        $query->whereScript($script);

        $rows = $query->fetchAll();
        $this->assertEquals(1, count($rows));
        $this->assertEquals(10, $rows[0]['id']);
    }


}
