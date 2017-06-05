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
     * Отправить запрос на конкретный endpoint elasticsearch-сервера
     * @param array $query
     * @return array
     */
    abstract protected function executeQuery(array $query);


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
     * @return Query $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Выполнить запрос к ES и вернуть результаты поиска.
     * @return SearchResult - возвращает набор документов
     */
    public function fetchAll()
    {
        $response = $this->fetchRaw();
        $result = $this->createSearchResult($response);
        return $result;
    }


    /**
     * Выполнить запрос к ES и вернуть необработанный результат (с мета-данными).
     * @return array возвращает необработанный ответ ES
     */
    public function fetchRaw()
    {
        // build query
        $query = $this->getQuery();

        $start = microtime(1);

        // send query to elastic
        $result = $this->executeQuery($query);

        // measure time
        $time = round((microtime(1) - $start) * 1000);

        return $result;
    }


    /**
     * Создать из ответа ES-сервера экземляр SearchResult
     * @param array $response
     * @return SearchResult
     */
    protected function createSearchResult(array $response)
    {
        $results  = $this->extractDocuments($response);
        $total    = $this->extractTotal($response);
        $searchResult = new SearchResult($results, $total);
        return $searchResult;
    }

    /**
     * Выбрать документы из ответа ES-сервера и добавить script fields.
     * @param array $response - ответ ES сервера.
     * @return array - возвращает набор документов
     */
    protected function extractDocuments(array $response)
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
    protected function extractTotal(array $response)
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