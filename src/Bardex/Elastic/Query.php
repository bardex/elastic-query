<?php

namespace Bardex\Elastic;


class Query implements \JsonSerializable
{
    /**
     * @var \Elasticsearch\Client $client
     */
    protected $elastic;

    /**
     * имя индекса модели в ES
     * @var string $index
     */
    protected $index;

    /**
     * имя типа модели в ES
     * @var string $type
     */
    protected $type;

    /**
     * параметры сортировки
     * @var array $orders
     */
    protected $orders = [];

    /**
     * фильтры выборки
     * @var array $filters
     */
    protected $filters = [];

    /**
     * сколько всего в индексе ES строк удовлетворяющих параметрам поиска
     * @var integer $totalResults
     */
    protected $totalResults;

    /**
     * сколько строк выбирать из индекса
     * @var int $limit
     */
    protected $limit = 10;

    /**
     * сколько строк пропустить
     * @var int $offset
     */
    protected $offset = 0;

    /**
     * Какие поля выводить
     * @var array
     */
    protected $includes;

    /**
     * Какие поля исключить из выборки
     * @var array
     */
    protected $excludes;

    /**
     * Вычисляемые поля в результатах
     * @var array
     */
    protected $scriptFields = [];

    /**
     * Логгер
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    public function __construct(\Elasticsearch\Client $elastic)
    {
        $this->elastic = $elastic;
        $this->logger = new \Psr\Log\NullLogger;
    }

    public function setLogger(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Установить имя индекса для поиска
     * @param $index
     * @return $this
     */
    public function setIndex($index)
    {
        $this->index = (string) $index;
        return $this;
    }

    /**
     * Установить имя типа для поиска
     * @param $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = (string) $type;
        return $this;
    }

    /**
     * Выводить перечисленные поля.
     * (не обязательный метод, по-умолчанию, выводятся все)
     * Методы select() и exclude() могут работать совместно.
     * @param array $fields
     * @return $this;
     * @example $q->select(['id', 'title', 'brand.id', 'brand.title']);
     */
    public function select(array $fields)
    {
        $this->includes = $fields;
        return $this;
    }


    /**
     * Добавить в результаты вычисляемое поле, на скриптовом языке painless или groovy
     * ```
     * $q->addScriptField('timeshift', 'return doc["tvpDouble.timeshift"].value * params.factor', ['factor' => 2]);
     * ```
     * Использование параметров рекомендуется, для увеличения производительности и эффективности компилирования скриптов.
     * @param string $fieldName - имя поля в результатах (если такое поле уже есть в документе, то оно будет заменено)
     * @param string $script - текст скрипта
     * @param array $params - параметры которые нужно передать в скрипт
     * @param string $lang - язык скрипта painless или groovy
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/search-request-script-fields.html
     * @return self $this
     */
    public function addScriptField($fieldName, $script, array $params = null, $lang = 'painless')
    {
        $item = [
            'script' => [
                'lang'   => $lang,
                'inline' => $script,
            ]
        ];
        if ($params) {
            $item['script']['params'] = $params;
        }
        $this->scriptFields[$fieldName] = $item;
        return $this;
    }

    /**
     * Удалить из выборки поля.
     * (не обязательный метод, по-умолчанию, выводятся все)
     * Методы select() и exclude() могут работать совместно.
     * @param array $fields
     * @return $this;
     * @example $q->exclude(['body', '*.body']);
     */
    public function exclude(array $fields)
    {
        $this->excludes = $fields;
        return $this;
    }

    /**
     * Добавить фильтр в raw формате, если готовые методы фильтрации не подходят.
     * Для удобства используй готовые методы фильтрации: where(), whereIn(), whereBetween(), whereMatch()
     * whereLess() и другие методы where*()
     *
     * @param $type - тип фильтрации (term|terms|match|range)
     * @param $filter - сам фильтр
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-terms-query.html
     * @return $this
     */
    public function addFilter($type, $filter)
    {
        $this->filters[] = [$type => $filter];
        return $this;
    }

    /**
     * Добавить фильтр ТОЧНОГО совпадения,
     * этот фильтр не влияет на поле релевантности _score.
     * Внимание! Класс Query не делает фильтрации или экранирования вводимых значений.
     *
     * @param $field - поле по которому фильтруем (id, brand.hasManySeries ...)
     * @param $value - искомое значение
     * @example $q->where('channel', 1)->where('tvpDouble.isDefault', 1);
     * @return $this;
     */
    public function where($field, $value)
    {
        $this->filters[] = ['term' => [$field => $value]];
        return $this;
    }

    /**
     * Добавить фильтр совпадения хотя бы одного значения из набора,
     * этот фильтр не влияет на поле релевантности _score.
     * Внимание! Класс Query не делает фильтрации или экранирования вводимых значений.
     *
     * @param $field - поле по которому фильтруем (id, brand.hasManySeries ...)
     * @param $values - массив допустимых значений
     * @example $q->whereIn('channel', [1,2,3])->where('tvpDouble.isDefault', 1);
     * @return $this;
     */
    public function whereIn($field, array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->filters[] = ['terms' => [$field => $values]];
        return $this;
    }

    /**
     * Добавить фильтр вхождения значение в диапазон (обе границы включительно)
     * Можно искать по диапазону дат
     * этот фильтр не влияет на поле релевантности _score.
     * Внимание! Класс Query не делает фильтрации или экранирования вводимых значений.
     *
     * @param $field - поле, по которому фильтруем (realDateStart, ...)
     * @param $min - нижняя граница диапазона (включительно)
     * @param $max - верхняя граница диапазона (включительно)
     * @param $dateFormat - необязательное поле описание формата даты
     * @example
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-range-query.html
     * @return $this;
     */
    public function whereBetween($field, $min, $max, $dateFormat = null)
    {
        $params = ['gte' => $min, 'lte' => $max];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->filters[] = ['range' => [$field => $params]];
        return $this;
    }

    /**
     * Добавить в фильтр сложное условие с вычислениями, на скриптовом языке painless или groovy
     * ```
     *  $q->whereScript('doc["brand.id"].value == params.id', ['id' => 5169]);
     * ```
     * Использование параметров рекомендуется, для увеличения производительности и эффективности компилирования скриптов
     *
     * @param string $script - строка скрипта
     * @param array $params - параматеры для скрипта
     * @param string $lang - язык painless или groovy
     * @return self $this;
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-script-query.html
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/modules-scripting-painless.html
     */
    public function whereScript($script, array $params = null, $lang = 'painless')
    {
        $item = [
            'script' => [
                'script' => [
                    'inline' => $script,
                    'lang'   => $lang
                ]
            ]
        ];
        if ($params) {
            $item['script']['script']['params'] = $params;
        }
        $this->filters[] = $item;
        return $this;
    }

    /**
     * добавить фильтр "больше или равно"
     * @param $field
     * @param $value
     * @param null $dateFormat
     * @return $this
     */
    public function whereGreaterOrEqual($field, $value, $dateFormat = null)
    {
        $params = ['gte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->filters[] = ['range' => [$field => $params]];
        return $this;
    }

    /**
     * добавить фильтр "больше чем"
     * @param $field
     * @param $value
     * @param null $dateFormat
     * @return $this
     */
    public function whereGreater($field, $value, $dateFormat = null)
    {
        $params = ['gt' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->filters[] = ['range' => [$field => $params]];
        return $this;
    }

    /**
     * добавить фильтр "меньше или равно"
     * @param $field
     * @param $value
     * @param null $dateFormat
     * @return $this
     */
    public function whereLessOrEqual($field, $value, $dateFormat = null)
    {
        $params = ['lte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->filters[] = ['range' => [$field => $params]];
        return $this;
    }

    /**
     * добавить фильтр "меньше чем"
     * @param $field
     * @param $value
     * @param null $dateFormat
     * @return $this
     */
    public function whereLess($field, $value, $dateFormat = null)
    {
        $params = ['lt' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->filters[] = ['range' => [$field => $params]];
        return $this;
    }


    /**
     * Добавить фильтр полнотекстового поиска
     * этот фильтр влияет на поле релевантности _score.
     * Внимание! Класс Query не делает фильтрации или экранирования вводимых значений.
     *
     * @param $field - поле по которому фильтруем (title, brand.title ...)
     * @param $text - поисковая фраза
     * @example $q->whereMatch('title', 'Олимпийский чемпион')->addOrderBy('_score', 'desc');
     * @return $this;
     */
    public function whereMatch($field, $text)
    {
        $this->filters[] = ['match' => [$field => $text]];
        return $this;
    }


    /**
     * Добавить поле сортировки.
     * Для сортировки по релевантности существует псевдополе _score (значение больше - релевантность лучше)
     * @param $field - поле сортировки
     * @param string $order - направление сортировки asc|desc
     * @example $q->addOrderBy('channel', 'asc')->addOrderBy('_score', 'desc');
     * @return $this
     */
    public function addOrderBy($field, $order = 'asc')
    {
        $field = (string) $field;
        $order = (string) $order;
        $this->orders[] = [$field => ['order' => $order]];
        return $this;
    }

    /**
     * Установить лимиты выборки
     * @param $limit - сколько строк выбирать
     * @param int $offset - сколько строк пропустить
     * @return $this;
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit  = (int) $limit;
        $this->offset = (int) $offset;
        return $this;
    }


    /**
     * Выполнить запрос к ES и вернуть результаты поиска.
     * Внимание! для экономии памяти результаты не хранятся в этом объекте, а сразу возвращаются.
     * Чтобы получить кол-во строк всего найденных в индексе (без учета лимита), используй метод getTotalResults()
     * @return array - возвращает набор документов
     */
    public function fetchAll()
    {
        $this->totalResults = 0;
        $results = [];
        $query  = $this->getQuery(); // build query
        $result = $this->elastic->search($query); // send query to elastic
        $result = $result['hits'];
        $this->totalResults = $result['total']; // total results
        foreach ($result['hits'] as $hit) {
            $row = $hit['_source'];
            if (isset($hit['fields'])) { // script fields
                foreach ($hit['fields'] as $field => $data) {
                    if (count($data) == 1) {
                        $row[$field] = array_shift($data);
                    }
                    else {
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
        }
        else {
            return null;
        }
    }


    /**
     * Количество документов всего найденных в индексе, для последнего запроса.
     * @return int
     */
    public function getTotalResults()
    {
        return $this->totalResults;
    }


    /**
     * Собрать запрос
     * @return array
     */
    public function getQuery()
    {
        $params = [
            'index' => $this->index,
            'type'  => $this->type,
            'body'  => [
                'query'   => [
                    'bool' => [
                        'must' => $this->filters,
                    ],
                ],
            ],
            'size'  => $this->limit,
            'from'  => $this->offset
        ];

        if ($this->orders) {
            $params['body']['sort'] = $this->orders;
        }

        if ($this->includes) {
            $params['body']['_source']['includes'] = $this->includes;
        }

        if ($this->excludes) {
            $params['body']['_source']['excludes'] = $this->excludes;
        }

        if (!isset($params['body']['_source'])) {
            $params['body']['_source'] = true;
        }

        if ($this->scriptFields) {
            $params['body']['script_fields'] = $this->scriptFields;
        }

        return $params;
    }

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