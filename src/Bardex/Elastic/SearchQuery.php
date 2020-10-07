<?php namespace Bardex\Elastic;

/**
 * Fluent interface for ElasticSearch
 * @package Bardex\Elastic
 */
class SearchQuery extends Query
{
    /**
     * Параметры запроса
     * @var array
     */
    protected $params = [];

    /**
     * @var Where
     */
    protected $whereHelper;


    public function __clone()
    {
        $this->whereHelper = new Where($this);
    }

    /**
     * @return SearchQuery
     */
    public function fork()
    {
        $copy = clone $this;
        return $copy;
    }

    /**
     * Установить имя индекса для поиска
     * @param string $index
     * @param string $type
     * @return self $this
     */
    public function setIndex($index, $type = null)
    {
        $this->params['index'] = (string)$index;
        if ($type) {
            $this->setType($type);
        }
        return $this;
    }

    /**
     * Установить имя типа для поиска
     * @param $type
     * @return self $this
     */
    public function setType($type)
    {
        $this->params['type'] = (string)$type;
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
     *
     * @param string $type - тип фильтрации (term|terms|match|range)
     * @param $filter - фильтр
     * @param string $context - контекст запроса Query::CONTEXT_*
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-terms-query.html
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     * @return self $this
     */
    public function addFilter($type, $filter, $context = Query::CONTEXT_DEFAULT)
    {
        if (!isset($this->params['body']['query']['bool'][$context])) {
            $this->params['body']['query']['bool'][$context] = [];
        }
        $this->params['body']['query']['bool'][$context][] = [$type => $filter];
        return $this;
    }

    /**
     * Добавить отрицательный фильтр в raw формате, если готовые методы фильтрации не подходят.
     *
     * @param string $type - тип фильтрации (term|terms|match|range)
     * @param $filter - фильтр
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     * @return self $this
     */
    public function addNotFilter($type, $filter)
    {
        if (!isset($this->params['body']['query']['bool']['must_not'])) {
            $this->params['body']['query']['bool']['must_not'] = [];
        }
        $this->params['body']['query']['bool']['must_not'][] = [$type => $filter];
        return $this;
    }


    /**
     * Создать фильтр.
     *
     * @param string $field - поле по которому фильтруем (id, page.categoryId...)
     * @param string $context - контекст запроса Query::CONTEXT_*
     * @example $query->where('channel')->equal(1)->where('page.categoryId')->in([10,12]);
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
     * @return Where;
     */
    public function where($field, $context = Query::CONTEXT_DEFAULT)
    {
        if (null === $this->whereHelper) {
            $this->whereHelper = new Where($this);
        }
        $this->whereHelper->init($field, $context);
        return $this->whereHelper;
    }


    /**
     * Добавить в фильтр сложное условие с вычислениями, на скриптовом языке painless или groovy.
     * Использование параметров рекомендуется, для увеличения производительности скриптов.
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
        $field = (string)$field;
        $order = (string)$order;
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
        $this->params['body']['size'] = (int)$limit;
        $this->params['body']['from'] = (int)$offset;
        return $this;
    }

    public function minScore($score)
    {
        $this->params['body']['min_score'] = $score;
        return $this;
    }

    /**
     * Включить подсветку найденного
     * @param array $fields - поля, которые будут подсвечиваться
     * @param int $fragment_size - размер подсвеченного фрагмента
     * @param array|string $pre_tags - открывающий(ие) тэг(и)
     * @param array|string $post_tags - закрывающий(ие) тэг(и)
     * @return $this
     */
    public function highlight($fields, $fragment_size=100, $pre_tags = '<em>', $post_tags = '</em>')
    {
        $arr = [];
        foreach ($fields as $field) {
            $arr[$field] = new stdClass();
        }

        $this->params['body']['highlight'] = [
            'fields' => $arr,
            'fragment_size'=> $fragment_size,
            'pre_tags' => $pre_tags,
            'post_tags' => $post_tags
        ];

        return $this;
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
     * @param bool $hydration
     *
     * @return array|SearchResult|mixed
     */
    public function fetchAll($hydration = true)
    {
        return $this->client->search($this->getQuery(), $hydration);
    }
}
