<?php namespace Bardex\Elastic;

class Hydrator implements IHydrator
{

    /**
     * Создать из ответа ElasticSearch экземпляр SearchResult
     * @param array $response
     * @return SearchResult
     */
    public function hydrateResponse(array $response)
    {
        $results = $this->extractDocuments($response);
        $total = $this->extractTotal($response);
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
                // script fields
                if (isset($hit['fields'])) {
                    foreach ($hit['fields'] as $field => $data) {
                        if (count($data) == 1) {
                            $row[$field] = array_shift($data);
                        } else {
                            $row[$field] = $data;
                        }
                    }
                }
                $row['__score'] = $hit['_score'];
                if ($this->filter($row)) {
                    $results[] = $row;
                }
            }
        }
        return $results;
    }

    protected function filter($row)
    {
        return $row;
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
}
