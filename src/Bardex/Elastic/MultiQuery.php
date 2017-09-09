<?php namespace Bardex\Elastic;

class MultiQuery extends Query
{
    protected $queryList = [];

    /**
     * @param string $alias
     * @return SearchQuery
     */
    public function createSearchQuery($alias)
    {
        $query = $this->client->createSearchQuery();
        $this->addQuery($alias, $query);
        return $query;
    }

    /**
     * @param string $alias
     * @param SearchQuery $query
     * @return MultiQuery
     */
    public function addQuery($alias, SearchQuery $query)
    {
        $this->queryList[$alias] = $query;
        return $this;
    }

    /**
     * @return array
     */
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

    /**
     * @param bool $hydration
     * @return SearchResult|mixed
     */
    public function fetchAll($hydration = true)
    {
        $responses = $this->client->msearch($this->getQuery(), $hydration);
        if ($hydration) {
            $aliases = array_keys($this->queryList);
            $results = [];
            foreach ($responses as $i => $response) {
                $alias = $aliases[$i];
                $results[$alias] = $response;
            }
            return new SearchResult($results, 0);
        } else {
            return $responses;
        }
    }
}
