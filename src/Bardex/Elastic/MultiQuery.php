<?php

namespace Bardex\Elastic;


class MultiQuery extends Query
{
    protected $queryList = [];

    public function addQuery($alias, SearchQuery $query)
    {
        $this->queryList[$alias] = $query;
        return $this;
    }

    protected function executeQuery(array $query)
    {
        return $this->elastic->msearch($query);
    }

    protected function createSearchResult(array $response)
    {
        $aliasMap = array_keys($this->queryList);
        $results = [];
        foreach ($response['responses'] as $queryNumber => $queryResponse) {
            $alias = $aliasMap[$queryNumber];
            $results[$alias] = parent::createSearchResult($queryResponse);
        }

        return new SearchResult($results, 0);
    }


    public function getQuery()
    {
        $params = ['body' => []];
        foreach ($this->queryList as $query) {
            $query = $query->getQuery();
            $params['body'][] = [
                'index' => $query['index'],
                'type'  => $query['type'],
            ];
            $params['body'][] = $query['body'];
        }
        return $params;
    }
}