<?php
namespace Bardex\Elastic;


class MultiQuery
{
    /**
     * @var \Elasticsearch\Client $client
     */
    protected $elastic;

    protected $queryList = [];

    /**
     * Логгер
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;


    public function __construct(\Elasticsearch\Client $elastic)
    {
        $this->elastic = $elastic;
        $this->logger = new \Psr\Log\NullLogger;
    }

    /**
     * @param Query $query
     * @return $this
     */
    public function addQuery( Query $query ) {
        $this->queryList[] = $query;
        return $this;
    }

    public function fetchAll()
    {
        $query = $this->getQuery();
        $rows = $this->elastic->msearch($query);
    }

    public function getQuery()
    {
        $params = ['body' => []];
        foreach ($this->queryList as $query)
        {
            $query = $query->getQuery();
            $params['body'][] = [
                'index' => $query['index'],
                'type'  => $query['type'],
            ];
            if (!empty($query['body']['query']))
            {
                $params['body'][] = [
                    'query' => $query['body']['query'],
                ];
            }
        }
        return $params;
    }
}