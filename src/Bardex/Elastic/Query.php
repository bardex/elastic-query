<?php

namespace Bardex\Elastic;


use Elasticsearch\Client as ElasticClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Query
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
abstract class Query implements \JsonSerializable
{
    /**
     * Логгер
     * @var LoggerInterface $logger
     */
    protected $logger;
    /**
     * @var ElasticClient $client
     */
    protected $elastic;

    /**
     * Получить собранный elasticsearch-запрос
     * @return array
     */
    abstract public function getQuery();


    /**
     * Конструктор
     * @param ElasticClient $elastic
     */
    public function __construct(ElasticClient $elastic)
    {
        $this->elastic = $elastic;
        $this->logger = new NullLogger;
    }


    /**
     * Добавить Psr-совместимый логгер
     * @param LoggerInterface $logger
     * @return SearchQuery $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Выбрать документы из ответа ES-сервера и добавить script fields.
     * @param array $response - ответ ES сервера.
     * @return array - возвращает набор документов
     */
    public function extractDocuments(array $response)
    {
        $results = [];
        if (isset($response['hits']['hits'])) {
            foreach ($response['hits']['hits'] as $hit) {
                $row = $hit['_source'];
                if (isset($hit['fields'])) { // script fields
                    foreach ($hit['fields'] as $field => $data) {
                        if (count($data) == 1) {
                            $row[$field] = array_shift($data);
                        } else {
                            $row[$field] = $data;
                        }
                    }
                }
                $results[] = $row;
            }
        }
        return $results;
    }


    /**
     * Выбрать из ответа ES-сервера количество найденных документов.
     * @param array $response - ответ ES сервера.
     * @return integer - возвращает количество найденных документов.
     */
    public function extractTotal(array $response)
    {
        $total = 0;
        if (isset($response['hits']['total'])) {
            $total = $response['hits']['total'];
        }
        return $total;
    }


    /**
     * Имплементация \JsonSerializable
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getQuery();
    }
}