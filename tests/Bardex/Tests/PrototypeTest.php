<?php

namespace Bardex\Tests;

use Bardex\Elastic\Listener\Logger as LoggerListener;
use Bardex\Elastic\MultiQuery;
use Bardex\Elastic\PrototypeQuery;
use Bardex\Elastic\SearchQuery;
use Psr\Log\AbstractLogger;

class PrototypeTest extends AbstractTestCase
{

    public function testSearchQuery()
    {
        $prototype = new PrototypeQuery(self::$client);

        $logger  = new LoggerListener($this->getMock(AbstractLogger::class));
        $logger->setLogSlowQueries(true);
        $logger->setSlowQueryLimitMs(100);
        $logger->setLogErrorQueries(true);
        $logger->setLogAllQueries(true);
        $prototype->addListener($logger);

        $searchQuery = $prototype->createSearchQuery();
        $this->assertInstanceOf(SearchQuery::class, $searchQuery);

        $this->assertTrue($prototype->getElasticClient() === $searchQuery->getElasticClient());
        $this->assertTrue($prototype->getListeners() === $searchQuery->getListeners());

        // factory must create new instance every time
        $searchQueryOther = $prototype->createSearchQuery();
        $this->assertFalse($searchQuery === $searchQueryOther);
    }


    public function testMultiQuery()
    {
        $prototype = new PrototypeQuery(self::$client);

        $logger  = new LoggerListener($this->getMock(AbstractLogger::class));
        $logger->setLogSlowQueries(true);
        $logger->setSlowQueryLimitMs(100);
        $logger->setLogErrorQueries(true);
        $logger->setLogAllQueries(true);
        $prototype->addListener($logger);

        $multiQuery = $prototype->createMultiQuery();
        $this->assertInstanceOf(MultiQuery::class, $multiQuery);

        $this->assertTrue($prototype->getElasticClient() === $multiQuery->getElasticClient());
        $this->assertTrue($prototype->getListeners() === $multiQuery->getListeners());

        // factory must create new instance every time
        $multiQueryOther = $prototype->createMultiQuery();
        $this->assertFalse($multiQuery === $multiQueryOther);
    }
}
