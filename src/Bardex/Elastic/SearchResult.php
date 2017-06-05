<?php

namespace Bardex\Elastic;


/**
 * SearchResult
 * @package Bardex\Elastic
 * @author Andrey Volynov <dubpubz@gmail.com>
 * @author Alexey Sumin <bardex@ya.ru>
 */
class SearchResult implements \ArrayAccess, \Countable, \IteratorAggregate
{
    /**
     * @var  int $totalCount
     */
    protected $totalCount;

    /**
     * @var array|array[]|object[] $results
     */
    protected $results = [];


    public function __construct($results, $totalCount)
    {
        $results = (array) $results;
        $this->results = $results;
        $this->totalCount = $totalCount;
    }

    /**
     * Returns first entry from result set, or null,
     * if result set is empty.
     *
     * @return null|array|object|array[]|object[]
     */
    public function getFirst()
    {
        if ($this->isEmpty()) {
            return null;
        }
        $keys = array_keys($this->results);
        return $this->results[$keys[0]];
    }

    /**
     * Returns the complete result set.
     *
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Method returns count of returned query results.
     *
     * @return int
     */
    public function count()
    {
        return count($this->results);
    }

    /**
     * Method returns count of total query results in index.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Method determines, if result data set is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return $this->count() == 0;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->results);
    }

    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->results[$offset] = $value;

        return $this;
    }

    public function offsetUnset($offset)
    {
        unset($this->results[$offset]);

        return $this;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }
}
