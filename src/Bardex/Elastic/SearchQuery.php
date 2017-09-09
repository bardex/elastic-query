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
     * @param $index
     * @return self $this
     */
    public function setIndex($index)
    {
        $this->params['index'] = (string)$index;
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
     * @param $field - поле по которому фильтруем (id, page.categoryId...)
     * @example $query->where('channel')->equal(1)->where('page.categoryId')->in([10,12]);
     * @return Where;
     */
    public function where($field)
    {
        if (null === $this->whereHelper) {
            $this->whereHelper = new Where($this);
        }
        $this->whereHelper->setField($field);
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

    public function fetchAll($hydration = true)
    {
        return $this->client->search($this->getQuery(), $hydration);
    }
}
