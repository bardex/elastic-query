<?php

namespace Bardex\Elastic\Listener;

use Bardex\Elastic\IListener;
use Psr\Log\LoggerInterface;

class Logger implements IListener
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var bool логировать все запросы
     */
    protected $logAllQueries=true;

    /**
     * @var bool логировать запросы с ошибками
     */
    protected $logErrorQueries=true;

    /**
     * @var bool логировать медленные запросы
     */
    protected $logSlowQueries=true;

    /**
     * @var int лимит времени выполнения запроса после которого он считается медленным (мс)
     */
    protected $slowQueryLimitMs = 1000;

    /**
     * Logger constructor.
     * @param LoggerInterface $logger
     * @param bool $logAllQueries
     * @param bool $logErrorQueries
     * @param bool $logSlowQueries
     * @param int $slowQueryLimitMs
     */
    public function __construct(
        LoggerInterface $logger,
        $logAllQueries=true,
        $logErrorQueries=true,
        $logSlowQueries=true,
        $slowQueryLimitMs=1000)
    {
        $this->logger = $logger;
        $this->logAllQueries = $logAllQueries;
        $this->logErrorQueries = $logErrorQueries;
        $this->logSlowQueries = $logSlowQueries;
        $this->slowQueryLimitMs = $slowQueryLimitMs;
    }


    public function onSuccess(array $query, array $response, $time)
    {

        // TODO: Implement onSuccess() method.
    }

    public function onError(array $query, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

}