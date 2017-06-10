PHP fluent interface for ElasticSearch
=======================================

[![Build Status](https://travis-ci.org/bardex/elastic-query.svg?branch=devel)](https://travis-ci.org/bardex/elastic-query)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bardex/elastic-query/badges/quality-score.png?b=devel)](https://scrutinizer-ci.com/g/bardex/elastic-query/?branch=devel)
[![Coverage Status](https://coveralls.io/repos/github/bardex/elastic-query/badge.svg?branch=devel&v=2)](https://coveralls.io/github/bardex/elastic-query?branch=devel&v=2)

REQUIREMENTS
------------
- PHP >= 5.5
- PHP Elasticsearch\Client ~2.0 or ~5.0 (only PHP >= 5.6.6)
- ElasticSearch server >= 5.0 

INSTALLATION
------------
```
$ composer require bardex/elastic-query
```

QUICK START
------------
```PHP
<?php

$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$query = new \Bardex\Elastic\SearchQuery($elastic);

$query->setIndex('products')
  ->setType('products')
  ->where('rubric')->in([1,5,7])
  ->where('price')->greater(0)
  ->where(['title','anons'])->match('game')
  ->exclude(['anons', 'comments']) // exclude fields
  ->setOrderBy('rating', 'desc')
  ->addOrderBy('dateCreation', 'desc')
  ->limit(30, 0);

// this is instance of \Bardex\Elastic\SearchResult
$results = $query->fetchAll();

// count of fetched results
$countResults = count($results);

// count of total found results
$totalFound = $results->getTotalFound();

// iterate results
foreach ($results as $result) {
    echo $result['id'] . ':' . $result['title'] . '<br>';
}

// get first result (or null if empty)
$first = $results->getFirst();

// nothing found ?
$results->isEmpty();

?>
```


USING MULTI-QUERY
-----------------
You can use MultiQuery to execute multiple search queries for one request to the server.
https://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html

```PHP
<?php

$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$posts = new \Bardex\Elastic\SearchQuery($elastic);
$posts->setIndex('posts')
  ->setType('posts')
  ->where('userId')->equal(1)
  ->where('status')->equal('published')
  ->setOrderBy('dateCreation', 'desc')
  ->limit(10, 0);

$user = new \Bardex\Elastic\SearchQuery($elastic);
$user->setIndex('users')
  ->setType('users')
  ->where('id')->equal(1);

$multi = new \Bardex\Elastic\MultiQuery($elastic);
$multi->addQuery('user', $user);
$multi->addQuery('posts', $posts);
// instance of \Bardex\Elastic\SearchQuery
$result = $multi->fetchAll();

// instance of \Bardex\Elastic\SearchQuery
$user  = $result['user'];
$posts = $result['posts'];
$totalPosts = $posts->getTotalFound();
?>
```

USING LISTENER FOR LOGGING
--------------------------
```PHP
<?php
$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$logger = new Logger; // some logger implemented \Psr\Log\LoggerInterface, like Monolog.
$logger->setFacility('elastic-query');
$listener = new \Bardex\Elastic\Listener\Logger($logger);
$listener->setLogAllQueries(true);   // debug log-level
$listener->setLogErrorQueries(true); // error log-level
$listener->setLogSlowQueries(true);  // warning log-level
$listener->setSlowQueryLimitMs(100);

$query = new \Bardex\Elastic\SearchQuery($elastic);
$query->addListener($listener);

$query->setIndex('products')
  ->setType('products')
  ->where('rubric')->in([1,5,7])
  ->where('price')->greater(0)
  ->addOrderBy('dateCreation', 'desc')
  ->limit(30, 0);

$query->fetchAll();
?>
```

USING A PROTOTYPE TO CREATE QUERIES
-----------------------------------
You can use one or more pre-configured prototypes for creating queries. 
You can declare a prototype in a container or service locator.

```PHP
<?php
$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$prototype = new \Bardex\Elastic\PrototypeQuery($elastic);

$logger = new Logger; // some logger implemented \Psr\Log\LoggerInterface, like Monolog.
$logger->setFacility('elastic-query');

$listener = new \Bardex\Elastic\Listener\Logger($logger);
$listener->setLogAllQueries(true);   // debug log-level
$listener->setLogErrorQueries(true); // error log-level
$listener->setLogSlowQueries(true);  // warning log-level
$listener->setSlowQueryLimitMs(100);
$prototype->addListener($listener);

$user = $prototype->createSearchQuery()
    ->setIndex('users')
    ->setType('users')
    ->where('id')->equal(1);

$posts = $prototype->createSearchQuery()
    ->setIndex('posts')
    ->setType('posts')
    ->where('user_id')->equal(1);


$multiQuery  = $prototype->createMultiQuery();
$multiQuery->addQuery('user', $user);
$multiQuery->addQuery('posts', $posts);
$results = $multiQuery->fetchAll();        

?>
```


AVAILABLE FILTERING METHODS
---------------------------
TODO

USING SCRIPT-FIELDS
-------------------
TODO
 
USING SCRIPT-FILTERS
-------------------
TODO


DEBUGGING
---------
TODO
