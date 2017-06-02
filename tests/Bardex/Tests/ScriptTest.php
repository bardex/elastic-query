<?php

namespace Bardex\Tests;

use \Bardex\Elastic\Query;
use \Bardex\Elastic\Script;

class ScriptTest extends AbstractTestCase
{
    public function testScriptCompile()
    {
        $script = new Script();
        $script->addLine('def a=10;');
        $script->addLine('return a * params.power;');
        $script->addParam('power', 10);

        $body   = $script->getBody();
        $params = $script->getParams();



    }




}
