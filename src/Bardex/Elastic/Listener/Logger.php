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

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param bool $logAllQueries
     */
    public function setLogAllQueries($logAllQueries)
    {
        $this->logAllQueries = $logAllQueries;
        return $this;
    }

    /**
     * @param bool $logErrorQueries
     */
    public function setLogErrorQueries($logErrorQueries)
    {
        $this->logErrorQueries = $logErrorQueries;
        return $this;
    }

    /**
     * @param bool $logSlowQueries
     */
    public function setLogSlowQueries($logSlowQueries)
    {
        $this->logSlowQueries = $logSlowQueries;
        return $this;
    }

    /**
     * @param int $slowQueryLimitMs
     */
    public function setSlowQueryLimitMs($slowQueryLimitMs)
    {
        $this->slowQueryLimitMs = $slowQueryLimitMs;
        return $this;
    }


    public function onSuccess(array $query, array $response, $time)
    {
        if ($this->logAllQueries || ($this->logSlowQueries && $time > $this->slowQueryLimitMs)) {
            $index = $this->getIndexName($query);
            $context = [
                'query' => json_encode($query),
                'time'  => $time,
                'time_range' => $this->getTimeRange($time),
                'index'      => $index,
                'found_rows'   => $response['hits']['total'],
                'fetched_rows' => count($response['hits']['hits'])
            ];

            if ($this->logAllQueries) {
                $this->logger->debug("Elastic query (index: $index, time: $time ms)", $context);
            }

            if ($this->logSlowQueries && $time > $this->slowQueryLimitMs) {
                $this->logger->warning("Slow elastic query (index: $index, time: $time ms)", $context);
            }
        }
    }

    public function onError(array $query, \Exception $e)
    {
        if ($this->logErrorQueries) {
            $index = $this->getIndexName($query);
            $context = [
                'query' => json_encode($query),
                'index' => $index,
                'error' => $e->getMessage(),
            ];
            $this->logger->error("Error elastic query (index: $index)", $context);
        }
    }

    protected function getIndexName(array $query)
    {
        $index = isset($query['index']) ? $query['index'] : '(undefined index)';
        $type  = isset($query['type']) ? $query['type'] : '(undefined type)';
        return "$index/$type";
    }

    protected function getTimeRange($time)
    {
        if ($time <= 10)   return '0-10 ms';
        if ($time <= 30)   return '10-30 ms';
        if ($time <= 50)   return '30-50 ms';
        if ($time <= 100)  return '50-100 ms';
        if ($time <= 500)  return '100-500 ms';
        if ($time <= 1000) return '500-1000 ms';
        if ($time > 1000)  return '> 1000 ms';
    }
}