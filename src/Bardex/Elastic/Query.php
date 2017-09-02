<?php namespace Bardex\Elastic;

/**
 * Class Query
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
abstract class Query
{
    const HYDRATE_RAW = 0;
    const HYDRATE_OBJECT = 1;

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

}
