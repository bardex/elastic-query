PHP fluent interface for ElasticSearch. Version 2.
=======================================


[![Build Status](https://travis-ci.org/bardex/elastic-query.svg?branch=v2)](https://travis-ci.org/bardex/elastic-query)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bardex/elastic-query/badges/quality-score.png?b=v2)](https://scrutinizer-ci.com/g/bardex/elastic-query/?branch=v2)
[![Coverage Status](https://coveralls.io/repos/github/bardex/elastic-query/badge.svg?branch=v2&v=2)](https://coveralls.io/github/bardex/elastic-query?branch=v2&v=2)

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
use Bardex\Elastic\Client;

// 1. Create client: 
$client = Client::create('localhost');

// OR
$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts(['localhost'])
           ->build();

$client = new Client($elastic);

// 2. Create search query
// this is instance of \Bardex\Elastic\SearchQuery
$query = $client->createSearchQuery(); 

$query->setIndex('shop', 'products')
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

//Fetch one column as array
$result->fetchColumn('id'); // ex. return [1,2,3] or []

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
use Bardex\Elastic\Client;

$client = Client::create('localhost');

$postsQuery = $client->createSearchQuery()
    ->setIndex('blog', 'posts')
    ->where('userId')->equal(1)
    ->where('status')->equal('published')
    ->setOrderBy('dateCreation', 'desc')
    ->limit(10, 0);

$userQuery = $client->createSearchQuery()
    ->setIndex('blog', 'users')
    ->where('id')->equal(1);

$results = $client->createMultiQuery()
    ->addQuery('posts', $postsQuery)
    ->addQuery('user', $userQuery)
    ->fetchAll();

$user  = $results['user'];
$posts = $results['posts'];
$totalPosts = $posts->getTotalFound();

// OR

$multi = $client->createMultiQuery();

$multi->createSearchQuery('posts')
    ->setIndex('blog', 'posts')
    ->where('userId')->equal(1)
    ->where('status')->equal('published')
    ->setOrderBy('dateCreation', 'desc')
    ->limit(10, 0);

$multi->createSearchQuery('user')
    ->setIndex('blog', 'users')
    ->where('id')->equal(1);
    
$results = $multi->fetchAll();
$user  = $results['user'];
$posts = $results['posts'];
?>
```


USING LISTENER FOR LOGGING
--------------------------
```PHP
<?php
use Bardex\Elastic\Client;


$client = Client::create('localhost');

// some logger implemented \Psr\Log\LoggerInterface, like Monolog.
$logger = new Logger; 
$logger->setFacility('elastic-query');

$log = new \Bardex\Elastic\Listener\Logger($logger);
$log->setLogAllQueries(true);   // debug log-level
$log->setLogErrorQueries(true); // error log-level
$log->setLogSlowQueries(true);  // warning log-level
$log->setSlowQueryLimitMs(100);

$client->addListener($log);

?>
```

USE OF A CUSTOM HYDRATOR
------------------------
```PHP
<?php
use Bardex\Elastic\Client;

$client = Client::create('localhost');

// hydrator must implements interface \Bardex\Elastic\IHydrator or extends \Bardex\Elastic\Hydrator
$hydrator = new CustomHydrator;
$client->setHydrator($hydrator);

?>
```


AVAILABLE FILTERING METHODS (in SearchQuery)
---------------------------

- equal($value)
- in([$v1,$v2,...])
- less($max)
- lessOrEqual($max)
- greater($min)
- greaterOrEqual($min)
- between($min, $max)
- match($text, $operator = 'or') - full-text search
- wildcard('rosy*')
- regexp('ro.*n')
- less($dateEnd, $dateFormat)
- lessOrEqual($dateEnd, $dateFormat)
- greater($dateStart, $dateFormat)
- greaterOrEqual($dateStart, $dateFormat)
- between($start, $end, $dateFormat)

- exists() - field exists and not empty

- not($value) - not equal
- notIn([$v1,$v2,...])
- notBetween($min, $max)
- notMatch($text) - text not match
- notExists() - field not exists or empty


Also see class \Bardex\Elastic\Where.  
Date format see https://www.elastic.co/guide/en/elasticsearch/reference/5.0/mapping-date-format.html  
Exists filter https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-exists-query.html  

*Examples*
```PHP
<?php
$query->where('id')->equal(10)
        ->where('category')->in([1,5,3])
        ->where(['title','anons'])->match('game') // full-text search by multi fields
        ->where('price')->between(100,1000) // min and max values included
        ->where('date_creation')->greater('2017-01-31T23:00:00+03:00', 'date_time_no_millis')
        ->where('refunds')->notExists();
?>
```

SEARCH CONTEXT
--------------
A query that matches documents matching boolean combinations of other queries. 
It is built using one or more boolean clauses, each clause with a typed occurrence. 
The occurrence types are:
- must - The clause (query) must appear in matching documents and will contribute to the score.
- filter - The clause (query) must appear in matching documents. However unlike must the score of the query will be ignored. 
Filter clauses are executed in filter context, meaning that scoring is ignored and clauses are considered for caching.
- should - The clause (query) should appear in the matching document. If the bool query is in a query context and has a
 must or filter clause then a document will match the bool query even if none of the should queries match. 
 In this case these clauses are only used to influence the score. If the bool query is a filter context or has neither
  must or filter then at least one of the should queries must match a document for it to match the bool query. 
   
By default used **must** context.

Also see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html

*Examples*
```PHP
<?php
use Bardex\Elastic\Query;

$query->where('id')->equal(10)
       ->where('category', Query::CONTEXT_FILTER)->equal(1)
       ->where('status', Query::CONTEXT_FILTER)->equal('published')
       ->where('title', Query::CONTEXT_SHOULD)->match('game') 
       ->where('tags', Query::CONTEXT_SHOULD)->in(['game', 'gamebox']) 
       ->setOrderBy(Query::ORDER_BY_SCORE, 'desc');
         
?>
```



FETCH SPECIFIED FIELDS 
----------------------
Methods select() and exclude() can be used together.
```PHP
<?php
    $query->select(['id', 'title', 'comments.*', 'categories.*'])
          ->exclude(['description', '*.description']);
?>
```

LIMIT FETCH DOCUMENTS 
----------------------
```PHP
<?php
    $query->limit($limit, $offset);
?>
```

SORT DOCUMENTS 
----------------------
```PHP
<?php
    $query->setOrderBy('date_creation', 'desc'); // clear old order and set new order
    $query->addOrderBy('rating', 'desc'); // add order
?>
```


USING SCRIPT-FIELDS
-------------------
Also see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-request-script-fields.html  
Method searchQuery::fetchAll() merge script fields with fields of documents. 
```PHP
<?php 
    // script return scalar
    $script = new \Bardex\Elastic\Script();
    $script->addLine('def a = 100;');
    $script->addLine('return a;'); // return scalar

    $query->addScriptField('fieldName', $script);
    $result = $query->fetchAll()->getFirst();
    echo $result['fieldName']; // 100
    
    // script return array
    $script = new \Bardex\Elastic\Script();
    $script->addLine('def a = 100;');
    $script->addLine('def b = 200;');
    $script->addLine('return [a,b];'); // return array
    
    $query->addScriptField('fieldName', $script);
    $result = $query->fetchAll()->getFirst();
    print_r($result['fieldName']); // [100, 200]
?>
```
Use documents values and script params.  
Also see:  
https://www.elastic.co/guide/en/elasticsearch/reference/current/modules-scripting-expression.html  
```PHP
<?php
    $script = new Script();
    $script->addLine("return  doc['id'].value * params.power;");
    $script->addParam('power', 1000);
    $query->addScriptField('pid', $script);
    $row = $query->fetchAll()->getFirst();
    echo $row['id']; // 2
    echo $row['pid']; // 2000
?>
```
 
USING SCRIPT-FILTERS
-------------------
Also see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-script-query.html  
```PHP
<?php
    $script = new Script();
    $script->addLine("return doc['price'].value * (1 + doc['tax'].value / 100) < params.max_price;");
    $script->addParam('max_price', 10000);
    $query->whereScript($script);
    $rows = $query->fetchAll();
?>
```


DEBUGGING
---------
Get prepared elastic query as php-array:
```PHP
<?php
    $query->getQuery();
?>
```
Get raw response from ElasticSearch server:
```PHP
<?php
    $query->fetchAll(false);
?>
```
