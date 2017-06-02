<?php

namespace Bardex\Tests;

use \Bardex\Elastic\Query;
use \Bardex\Elastic\Script;

class ScriptTest extends AbstractTestCase
{
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
}
