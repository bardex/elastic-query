<?php

namespace Bardex\Elastic;


/**
 * Class Query
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
abstract class Query extends PrototypeQuery
{
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

        // send query to elastic
        $start  = microtime(1);

        try {
            $result = $this->executeQuery($query);
            $time   = round((microtime(1) - $start) * 1000);
            $this->triggerSuccess($query, $result, $time);
            return $result;
        }
        catch (\Exception $e) {
            $this->triggerError($query, $e);
            throw $e;
        }
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

    protected function triggerSuccess(array $query, array $response, $time)
    {
        foreach ($this->listeners as $listener) {
            $listener->onSuccess($query, $response, $time);
        }
    }

    protected function triggerError(array $query, \Exception $e)
    {
        foreach ($this->listeners as $listener) {
            $listener->onError($query, $e);
        }
    }
}