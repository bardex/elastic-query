<?php

namespace Bardex\Tests;

use Bardex\Elastic\Listener\Logger as LoggingListener;
use Psr\Log\AbstractLogger;

class LoggerListenerTest extends AbstractTestCase
{
    protected static $testQuery = [
        'index' => 'test_index',
        'type'  => 'test_type'
    ];

    protected static $testResponse = [
        'hits' => [
            'total' => 10,
            'hits'  => [
                ['id' => 1],
                ['id' => 2],
            ]
        ]
    ];

    public function testLogAllQueriesOn()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(true);
        $listener->setLogErrorQueries(false);
        $listener->setLogSlowQueries(false);

        $logger->expects($this->once())->method('debug');
        $logger->expects($this->never())->method('error');
        $logger->expects($this->never())->method('warning');

        $listener->onSuccess(self::$testQuery, self::$testResponse, 10);
    }

    public function testLogAllQueriesOff()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(false);
        $listener->setLogErrorQueries(false);
        $listener->setLogSlowQueries(false);

        $logger->expects($this->never())->method('debug');
        $logger->expects($this->never())->method('error');
        $logger->expects($this->never())->method('warning');

        $listener->onSuccess(self::$testQuery, self::$testResponse, 10);
    }

    public function testLogSlowQueriesOn()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(false);
        $listener->setLogErrorQueries(false);
        $listener->setLogSlowQueries(true); // log slow queries
        $listener->setSlowQueryLimitMs(100);

        $logger->expects($this->never())->method('debug');
        $logger->expects($this->never())->method('error');
        $logger->expects($this->once())->method('warning'); // once slow query

        $listener->onSuccess(self::$testQuery, self::$testResponse, 200); // slow query
        $listener->onSuccess(self::$testQuery, self::$testResponse, 10); // fast query
    }

    public function testLogSlowQueriesOff()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(false);
        $listener->setLogErrorQueries(false);
        $listener->setLogSlowQueries(false);
        $listener->setSlowQueryLimitMs(100);

        $logger->expects($this->never())->method('debug');
        $logger->expects($this->never())->method('error');
        $logger->expects($this->never())->method('warning'); // log slow queries is off

        $listener->onSuccess(self::$testQuery, self::$testResponse, 200); // slow query
        $listener->onSuccess(self::$testQuery, self::$testResponse, 10); // fast query
    }

    public function testLogErrorOn()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(false);
        $listener->setLogErrorQueries(true); // log error queries is on
        $listener->setLogSlowQueries(false);

        $logger->expects($this->never())->method('debug');
        $logger->expects($this->once())->method('error');
        $logger->expects($this->never())->method('warning');

        $query = ['index' => 'test'];
        $e = new \Exception("Error query: type is undefined");

        $listener->onError(self::$testQuery, $e);
    }

    public function testLogErrorOff()
    {
        $logger = $this->getMock(AbstractLogger::class);
        $listener = new LoggingListener($logger);
        $listener->setLogAllQueries(false);
        $listener->setLogErrorQueries(false); // log error queries is off
        $listener->setLogSlowQueries(false);

        $logger->expects($this->never())->method('debug');
        $logger->expects($this->never())->method('error');
        $logger->expects($this->never())->method('warning');

        $query = ['index' => 'test'];
        $e = new \Exception("Error query: type is undefined");

        $listener->onError(self::$testQuery, $e);
    }
}
