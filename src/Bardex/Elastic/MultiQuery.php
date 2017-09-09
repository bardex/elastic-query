<?php namespace Bardex\Elastic;

class MultiQuery extends Query
{
    protected $queryList = [];

    public function addQuery(SearchQuery $query)
    {
        $this->queryList[] = $query;
        return $this;
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

    public function fetchAll($hydration=true)
    {
        return $this->client->msearch($this->getQuery(), $hydration);
    }

}
