<?php namespace Bardex\Elastic;

use Elasticsearch\Client as ElasticClient;

/**
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
class PrototypeQuery
{
    /**
     * @var ElasticClient $client
     */
    protected $elastic;

    /**
     * @var IListener[]
     */
    protected $listeners = [];


    /**
     * @param ElasticClient $elastic
     */
    public function __construct(ElasticClient $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * @return ElasticClient
     */
    public function getElasticClient()
    {
        return $this->elastic;
    }

    /**
     * @param $elastic
     */
    public function setElasticClient($elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * @return IListener[]
     */
    public function getListeners()
    {
        return $this->listeners;
    }

    /**
     * Create new instance of SearchQuery
     * @return SearchQuery
     */
    public function createSearchQuery()
    {
        $query = new SearchQuery($this->elastic);
        $query->listeners = $this->listeners;
        return $query;
    }

    /**
     * Create new instance of MultiQuery
     * @return MultiQuery
     */
    public function createMultiQuery()
    {
        $query = new MultiQuery($this->elastic);
        $query->listeners = $this->listeners;
        return $query;
    }

    /**
     * @param IListener $listener
     * @return $this
     */
    public function addListener(IListener $listener)
    {
        $this->listeners[] = $listener;
        return $this;
    }

    /**
     * @param IListener $listener
     * @return $this
     */
    public function removeListener(IListener $listener)
    {
        foreach ($this->listeners as $i => $listItem) {
            if ($listener === $listItem) {
                unset($this->listeners[$i]);
            }
        }
        return $this;
    }
}
