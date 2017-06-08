# PHP Elastic Query
## [devel]

[![Build Status](https://travis-ci.org/bardex/elastic-query.svg?branch=devel)](https://travis-ci.org/bardex/elastic-query)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bardex/elastic-query/badges/quality-score.png?b=devel)](https://scrutinizer-ci.com/g/bardex/elastic-query/?branch=devel)
[![Coverage Status](https://coveralls.io/repos/github/bardex/elastic-query/badge.svg?branch=devel&v=2)](https://coveralls.io/github/bardex/elastic-query?branch=devel&v=2)

## Examples
Simple search

```
<?php

$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$query = new \Bardex\Elastic\SearchQuery($elastic);

$query->setIndex('products')
  ->setType('products')
  ->where('rubric')->in([1,5,7])
  ->where('price')->greater(0)
  ->where(['title','anons'])->match('погремушка')
  ->where('status')->not(0)
  ->where('tags')->notIn([1,2,3])
  ->exclude(['anons', 'comments.*'])
  ->setOrderBy('rating', 'desc')
  ->addOrderBy('dateCreation', 'desc')
  ->limit(30, 0);

$result = $query->fetchAll();
$totalFound = $result->getTotalCount();
?>
```


Multi-query
```
<?php

$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$posts = new \Bardex\Elastic\SearchQuery($elastic);
$posts->setIndex('posts')
  ->setType('posts')
  ->where('userId')->in([1,5,7])
  ->setOrderBy('dateCreation', 'desc')
  ->limit(100, 0);

$users = new \Bardex\Elastic\SearchQuery($elastic);
$users->setIndex('users')
  ->setType('users')
  ->where('id')->in([1,5,7])
  ->limit(3, 0);

$multi = new \Bardex\Elastic\MultiQuery($elastic);
$multi->addQuery('users', $users);
$multi->addQuery('posts', $posts);
$result = $multi->fetchAll();

$users = $result['users'];
$posts = $result['posts'];
?>
```

