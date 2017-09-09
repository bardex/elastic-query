<?php namespace Bardex\Elastic;

interface IHydrator
{
    /**
     * Создать из ответа ElasticSearch экземпляр SearchResult
     * @param array $response
     * @return SearchResult
     */
    public function hydrateResponse(array $response);
}
