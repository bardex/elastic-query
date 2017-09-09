<?php namespace Bardex\Elastic;

/**
 * Class Query
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
abstract class Query
{
    /** @var Client */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Получить собранный elasticsearch-запрос
     * @return array
     */
    abstract public function getQuery();

    /**
     * @param bool $hydration
     * @return SearchResult|array
     */
    abstract public function fetchAll($hydration = true);

}
