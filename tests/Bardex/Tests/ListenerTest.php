<?php

namespace Bardex\Tests;

use \Bardex\Elastic\SearchQuery;
use \Bardex\Elastic\Script;

class ListenerTest extends AbstractTestCase
{
    protected static $testdata = [
        [
            'id' => 20,
            'title' => 'title Alice record',
            'channels' => [1,2,3],
            'publicDate' => '2017-01-01T00:00:00+03:00',
        ]
    ];

    public function testOnSuccessListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();
        $query->addListener($listener);
        $listener->expects($this->once())->method('onSuccess');
        $listener->expects($this->never())->method('onError');
        $query->fetchAll();
    }


    public function testRemoveListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();
        $query->addListener($listener);
        $query->removeListener($listener);
        $listener->expects($this->never())->method('onSuccess');
        $listener->expects($this->never())->method('onError');
        $query->fetchAll();
    }


    public function testOnErrorListener()
    {
        $listener = $this->getMock(\Bardex\Elastic\IListener::class);
        $query = $this->createQuery();
        $query->setType('fake_type')->setOrderBy('id'); // query with error
        $query->addListener($listener);
        $listener->expects($this->once())->method('onError');
        $listener->expects($this->never())->method('onSuccess');

        try {
            $query->fetchAll(); // query with error
        }
        catch (\Exception $e) {
        }
    }

}
