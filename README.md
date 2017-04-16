# PHP Elastic Query

[![Build Status](https://travis-ci.org/bardex/elastic-query.svg?branch=devel)](https://travis-ci.org/bardex/elastic-query)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bardex/elastic-query/badges/quality-score.png?b=devel)](https://scrutinizer-ci.com/g/bardex/elastic-query/?branch=devel)

## Examples

### DEVEL

```
<?php

$elastic = \Elasticsearch\ClientBuilder::create()
           ->setHosts('localhost')
           ->build();

$q = new \Bardex\Elastic\Query($elastic);

$q->setIndex('products')
  ->setType('products')
  ->whereIn('rubric', [1,5,7])
  ->whereGreater('price', 0)
  ->whereMatch('title', 'погремушка')
  ->exclude(['anons', 'comments.*'])
  ->addOrderBy('dateCreation', 'desc')
  ->limit(30, 0);

$results = $q->fetchAll();
$total   = $q->getTotalResults();


```