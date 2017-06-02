<?php

namespace Bardex\Elastic;


/**
 * Fluent interface for elasticsearch
 * @package Bardex\Elastic
 */
class Query implements \JsonSerializable
{
    /**
     * @var \Elasticsearch\Client $client
     */
    protected $elastic;

    /**
     * Параметры запроса
     * @var array
     */
    protected $params = [];

    /**
     * сколько всего в индексе ES строк удовлетворяющих параметрам поиска
     * @var integer $totalResults
     */
    protected $totalResults;

    /**
     * Логгер
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * Конструктор
     * @param \Elasticsearch\Client $elastic
     */
    public function __construct(\Elasticsearch\Client $elastic)
    {
        $this->elastic = $elastic;
        $this->logger = new \Psr\Log\NullLogger;
    }

    /**
     * Добавить Psr-совместимый логгер
     * @param \Psr\Log\LoggerInterface $logger
     * @return self $this
     */
    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Установить имя индекса для поиска
     * @param $index
     * @return self $this
     */
    public function setIndex($index)
    {
        $this->params['index'] = (string) $index;
        return $this;
    }


    /**
     * Установить имя типа для поиска
     * @param $type
     * @return self $this
     */
    public function setType($type)
    {
        $this->params['type'] = (string) $type;
        return $this;
    }


    /**
     * Выводить перечисленные поля.
     * (не обязательный метод, по-умолчанию, выводятся все)
     * Методы select() и exclude() могут работать совместно.
     * @param array $fields
     * @return self $this;
     * @example $query->select(['id', 'title', 'brand.id', 'brand.title']);
     */
    public function select(array $fields)
    {
        $this->params['body']['_source']['includes'] = $fields;
        return $this;
    }


    /**
     * Добавить в результаты вычисляемое поле, на скриптовом языке painless или groovy
     * @param string $fieldName - имя поля в результатах (если такое поле уже есть в документе, то оно будет заменено)
     * @param Script $script - скрипт
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/search-request-script-fields.html
     * @return self $this
     */
    public function addScriptField($fieldName, Script $script)
    {
        $this->params['body']['script_fields'][$fieldName] = $script->compile();
        return $this;
    }


    /**
     * Удалить из выборки поля.
     * (не обязательный метод, по-умолчанию, выводятся все)
     * Методы select() и exclude() могут работать совместно.
     * @param array $fields
     * @return self $this;
     * @example $query->exclude(['anons', '*.anons']);
     */
    public function exclude(array $fields)
    {
        $this->params['body']['_source']['excludes'] = $fields;
        return $this;
    }


    /**
     * Добавить фильтр в raw формате, если готовые методы фильтрации не подходят.
     * Для удобства используй готовые методы фильтрации: where(), whereIn(), whereBetween(), whereMatch()
     * whereLess() и другие методы where*()
     *
     * @param string $type - тип фильтрации (term|terms|match|range)
     * @param $filter - фильтр
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-terms-query.html
     * @return self $this
     */
    public function addFilter($type, $filter)
    {
        if (!isset($this->params['body']['query']['bool']['must'])) {
            $this->params['body']['query']['bool']['must'] = [];
        }
        $this->params['body']['query']['bool']['must'][] = [$type => $filter];
        return $this;
    }


    /**
     * Добавить фильтр точного совпадения, этот фильтр не влияет на поле релевантности _score.
     *
     * @param $field - поле по которому фильтруем (id, page.categoryId...)
     * @param $value - искомое значение
     * @example $query->where('channel', 1)->where('page.categoryId', 10);
     * @return self $this;
     */
    public function where($field, $value)
    {
        $this->addFilter('term', [$field => $value]);
        return $this;
    }


    /**
     * Добавить фильтр совпадения хотя бы одного значения из набора, этот фильтр не влияет на поле релевантности _score.
     *
     * @param $field - поле по которому фильтруем
     * @param $values - массив допустимых значений
     * @example $query->whereIn('channel', [1,2,3])->whereIn('page.categoryId', [10,11]);
     * @return self $this;
     */
    public function whereIn($field, array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->addFilter('terms', [$field => $values]);
        return $this;
    }


    /**
     * Добавить фильтр вхождения значение в диапазон (обе границы включительно).
     * Можно искать по диапазону дат.
     * Этот фильтр не влияет на поле релевантности _score.
     *
     * @param $field - поле, по которому фильтруем
     * @param $min - нижняя граница диапазона (включительно)
     * @param $max - верхняя граница диапазона (включительно)
     * @param $dateFormat - необязательное поле описание формата даты
     * @example $q->whereBetween('created', '01/01/2010', '01/01/2011', 'dd/MM/yyyy');
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-range-query.html
     * @return self $this;
     */
    public function whereBetween($field, $min, $max, $dateFormat = null)
    {
        $params = ['gte' => $min, 'lte' => $max];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->addFilter('range', [$field => $params]);
        return $this;
    }


    /**
     * Добавить в фильтр сложное условие с вычислениями, на скриптовом языке painless или groovy.
     * Использование параметров рекомендуется, для увеличения производительности и эффективности компилирования скриптов.
     *
     * @param Script $script - скрипт
     * @return self $this;
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-script-query.html
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/modules-scripting-painless.html
     */
    public function whereScript(Script $script)
    {
        $this->addFilter('script', $script->compile());
        return $this;
    }


    /**
     * Добавить фильтр "больше или равно"
     * @param $field - поле
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return self $this
     * @example $query->whereGreaterOrEqual("price", 100)
     * @example $query->whereGreaterOrEqual("created", "31/12/2016" , "dd/MM/yyyy")
     * @example $query->whereGreaterOrEqual("seller.rating", 4)
     */
    public function whereGreaterOrEqual($field, $value, $dateFormat = null)
    {
        $params = ['gte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->addFilter('range', [$field => $params]);
        return $this;
    }

    /**
     * Добавить фильтр "больше чем"
     * @param $field - поле
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return self $this
     * @example $query->whereGreater("price", 100)
     * @example $query->whereGreater("created", "31/12/2016" , "dd/MM/yyyy")
     * @example $query->whereGreater("seller.rating", 4)
     */
    public function whereGreater($field, $value, $dateFormat = null)
    {
        $params = ['gt' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->addFilter('range', [$field => $params]);
        return $this;
    }

    /**
     * Добавить фильтр "меньше или равно"
     * @param $field - поле
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return self $this
     * @example $query->whereLessOrEqual("price", 100)
     * @example $query->whereLessOrEqual("created", "31/12/2016" , "dd/MM/yyyy")
     * @example $query->whereLessOrEqual("seller.rating", 4)
     */
    public function whereLessOrEqual($field, $value, $dateFormat = null)
    {
        $params = ['lte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->addFilter('range', [$field => $params]);
        return $this;
    }


    /**
     * Добавить фильтр "меньше чем"
     * @param $field - поле
     * @param $value - значение
     * @param null $dateFormat - - необязательный формат даты
     * @return self $this
     * @example $query->whereLess("price", 100)
     * @example $query->whereLess("created", "31/12/2016" , "dd/MM/yyyy")
     * @example $query->whereLess("seller.rating", 4)
     */
    public function whereLess($field, $value, $dateFormat = null)
    {
        $params = ['lt' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->addFilter('range', [$field => $params]);
        return $this;
    }


    /**
     * Добавить фильтр полнотекстового поиска, этот фильтр влияет на поле релевантности _score.
     *
     * @param string|arary $field - поле (или массив полей) по которому ищем
     * @param $text - поисковая фраза
     * @example $query->whereMatch('title', 'яблочная слойка')->setOrderBy('_score', 'desc');
     * @example $query->whereMatch(['title', 'anons'], 'яблочная слойка')->setOrderBy('_score', 'desc');
     * @return self $this;
     */
    public function whereMatch($field, $text)
    {
        if (is_array($field)) {
            $this->addFilter('multi_match', [
                    'query'  => $text,
                    'fields' => $field
                ]);
        } else {
            $this->addFilter('match', [$field => $text]);
        }
        return $this;
    }

    /**
     * Установить поле сортировки.
     * Для сортировки по релевантности существует псевдополе _score (значение больше - релевантность лучше)
     * @param $field - поле сортировки
     * @param string $order - направление сортировки asc|desc
     * @example $query->setOrderBy('_score', 'desc');
     * @return self $this
     */
    public function setOrderBy($field, $order = 'asc')
    {
        $this->params['body']['sort'] = [];
        $this->addOrderBy($field, $order);
        return $this;
    }

    /**
     * Добавить поле сортировки.
     * Для сортировки по релевантности существует псевдополе _score (значение больше - релевантность лучше)
     * @param $field - поле сортировки
     * @param string $order - направление сортировки asc|desc
     * @example $query->addOrderBy('_score', 'desc');
     * @example $query->addOrderBy('seller.rating', 'desc');
     * @return self $this
     */
    public function addOrderBy($field, $order = 'asc')
    {
        $field = (string) $field;
        $order = (string) $order;
        if (!isset($this->params['body']['sort'])) {
            $this->params['body']['sort'] = [];
        }
        $this->params['body']['sort'][] = [$field => ['order' => $order]];
        return $this;
    }


    /**
     * Установить лимиты выборки
     * @param $limit - сколько строк выбирать
     * @param int $offset - сколько строк пропустить
     * @return self $this;
     */
    public function limit($limit, $offset = 0)
    {
        $this->params['body']['size'] = (int) $limit;
        $this->params['body']['from'] = (int) $offset;
        return $this;
    }


    /**
     * Выполнить запрос к ES и вернуть необработанный результат (с мета-данными).
     * Внимание! для экономии памяти результаты не хранятся в этом объекте, а сразу возвращаются.
     * Чтобы получить кол-во строк всего найденных в индексе (без учета лимита), используй метод getTotalResults()
     * @return array возвращает необработанный ответ ES
     */
    public function fetchRaw()
    {
        $this->totalResults;

        // build query
        $query  = $this->getQuery();

        // send query to elastic
        $start  = microtime(1);

        $result = $this->elastic->search($query);

        // measure time
        $time   = round((microtime(1) - $start) * 1000);

        // total results
        $this->totalResults = $result['hits']['total'];

        // log
        $index = $this->params['index'].'/'.$this->params['type'];
        $context = [
            'type'  => 'elastic',
            'query' => json_encode($query),
            'time'  => $time,
            'index' => $index,
            'found_rows'   => $this->totalResults,
            'fetched_rows' => count($result['hits']['hits'])
        ];

        $this->logger->debug("Elastic query (index: $index, time: $time ms)", $context);

        return $result;
    }


    /**
     * Выполнить запрос к ES и вернуть результаты поиска.
     * Внимание! для экономии памяти результаты не хранятся в этом объекте, а сразу возвращаются.
     * Чтобы получить кол-во строк всего найденных в индексе (без учета лимита), используй метод getTotalResults()
     * @return array - возвращает набор документов
     */
    public function fetchAll()
    {
        $result = $this->fetchRaw();

        $results = [];
        foreach ($result['hits']['hits'] as $hit) {
            $row = $hit['_source'];
            if (isset($hit['fields'])) { // script fields
                foreach ($hit['fields'] as $field => $data) {
                    if (count($data) == 1) {
                        $row[$field] = array_shift($data);
                    } else {
                        $row[$field] = $data;
                    }
                }
            }
            $results[] = $row;
        }

        return $results;
    }


    /**
     * Выполнить запрос к ES и вернуть первый результат.
     * Внимание! для экономии памяти результаты не хранятся в этом объекте, а сразу возвращаются.
     * Чтобы получить кол-во строк всего найденных в индексе (без учета лимита), используй метод getTotalResults()
     * @return array|null возращает первый найденный документ или null.
     */
    public function fetchOne()
    {
        $results = $this->fetchAll();
        if (count($results)) {
            return array_shift($results);
        } else {
            return null;
        }
    }


    /**
     * Количество документов всего найденных в индексе, для последнего запроса.
     * @return integer количество найденных документов
     */
    public function getTotalResults()
    {
        return $this->totalResults;
    }


    /**
     * Получить собранный запрос
     * @return array
     */
    public function getQuery()
    {
        $params = $this->params;

        if (!isset($params['body']['_source'])) {
            $params['body']['_source'] = true;
        }

        return $params;
    }

    /**
     * Имплементация \JsonSerializable
     * @return array
     */
    public function jsonSerialize() {
        return $this->getQuery();
    }


    /**
     * Получить JSON-дамп запроса для отладки
     * @return string
     */
    public function getJsonQuery()
    {
        return json_encode($this);
    }
}