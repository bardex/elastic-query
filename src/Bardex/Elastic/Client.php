<?php namespace Bardex\Elastic;

use Elasticsearch\Client as ElasticClient;
use Elasticsearch\ClientBuilder as ElasticClientBuilder;

/**
 * @package Bardex\Elastic
 * @author Alexey Sumin <bardex@ya.ru>
 */
class Client
{
    /** @var ElasticClient $client */
    protected $elastic;

    /** @var IListener[] */
    protected $listeners = [];

    /** @var  IHydrator */
    protected $hydrator;


    /**
     * @param ElasticClient $elastic
     */
    public function __construct(ElasticClient $elastic)
    {
        $this->elastic = $elastic;
    }

    /**
     * @param string|array $host
     * @return Client
     */
    public static function create($host)
    {
        $es = ElasticClientBuilder::create()
            ->setHosts((array)$host)
            ->build();

        return new static($es);
    }


    /**
     * @param IHydrator $hydrator
     */
    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return IHydrator
     */
    public function getHydrator()
    {
        if (null === $this->hydrator) {
            $this->hydrator = new Hydrator;
        }
        return $this->hydrator;
    }

    /**
     * Create new instance of SearchQuery
     * @return SearchQuery
     */
    public function createSearchQuery()
    {
        $query = new SearchQuery($this);
        return $query;
    }

    /**
     * Create new instance of MultiQuery
     * @return MultiQuery
     */
    public function createMultiQuery()
    {
        $query = new MultiQuery($this);
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

    public function query($endpoint, array $query)
    {
        // send query to elastic
        $start = microtime(1);

        try {
            $result = call_user_func([$this->elastic, $endpoint], $query);
            $time = round((microtime(1) - $start) * 1000);
            $this->triggerSuccess($query, $result, $time);
            return $result;
        } catch (\Exception $e) {
            $this->triggerError($query, $e);
            throw $e;
        }
    }

    public function search(array $query)
    {
        $response = $this->query('search', $query);
        $result = $this->getHydrator()->hydrateResponse($response);
        return $result;
    }

    public function msearch(array $query)
    {
        $responses = $this->query('msearch', $query);
        $results = [];
        foreach ($responses['responses'] as $response) {
            $results[] = $this->getHydrator()->hydrateResponse($response);
        }
        return new SearchResult($results, 0);
    }
}
