<?php namespace Bardex\Elastic;

class Where
{
    /** @var  string $field */
    protected $field;

    /** @var SearchQuery $query */
    protected $query;

    /** @var  string $context */
    protected $context;

    public function __construct(SearchQuery $query)
    {
        $this->query = $query;
    }

    public function init($field, $context = Query::CONTEXT_DEFAULT)
    {
        $this->setField($field);
        $this->setContext($context);
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param $value
     * @return SearchQuery
     */
    public function equal($value)
    {
        $this->query->addFilter('term', [$this->field => $value], $this->context);
        return $this->query;
    }

    /**
     * Добавить фильтр совпадения хотя бы одного значения из набора, этот фильтр не влияет на поле релевантности _score.
     *
     * @param $values - массив допустимых значений
     * @example $query->where('channel')->in([1,2,3])->where('page.categoryId')->in([10,11]);
     * @return SearchQuery;
     */
    public function in(array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->query->addFilter('terms', [$this->field => $values], $this->context);
        return $this->query;
    }

    /**
     * Добавить фильтр вхождения значение в диапазон (обе границы включительно).
     * Можно искать по диапазону дат.
     * Этот фильтр не влияет на поле релевантности _score.
     *
     * @param $min - нижняя граница диапазона (включительно)
     * @param $max - верхняя граница диапазона (включительно)
     * @param $dateFormat - необязательное поле описание формата даты
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-range-query.html
     * @return SearchQuery;
     */
    public function between($min, $max, $dateFormat = null)
    {
        $this->range(['gte' => $min, 'lte' => $max], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "больше или равно"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function greaterOrEqual($value, $dateFormat = null)
    {
        $this->range(['gte' => $value], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "больше чем"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function greater($value, $dateFormat = null)
    {
        $this->range(['gt' => $value], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "меньше или равно"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function lessOrEqual($value, $dateFormat = null)
    {
        $this->range(['lte' => $value], $dateFormat);
        return $this->query;
    }


    /**
     * Добавить фильтр "меньше чем"
     * @param $value - значение
     * @param null $dateFormat - - необязательный формат даты
     * @return SearchQuery
     */
    public function less($value, $dateFormat = null)
    {
        $this->range(['lt' => $value], $dateFormat);
        return $this->query;
    }


    protected function range($params, $dateFormat = null)
    {
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params], $this->context);
        return $this->query;
    }


    /**
     * Добавить фильтр полнотекстового поиска, этот фильтр влияет на поле релевантности _score.
     *
     * @param $text - поисковая фраза
     * @return SearchQuery;
     */
    public function match($text)
    {
        if (is_array($this->field)) {
            $this->query->addFilter('multi_match', [
                'query' => $text,
                'fields' => $this->field
            ], $this->context);
        } else {
            $this->query->addFilter('match', [$this->field => $text], $this->context);
        }
        return $this->query;
    }

    /**
     * Поле существует и имеет не null значение
     * @return SearchQuery
     */
    public function exists()
    {
        $this->query->addFilter('exists', ["field" => $this->field], $this->context);
        return $this->query;
    }

    /**
     * @param $value
     * @return SearchQuery
     */
    public function not($value)
    {
        $this->query->addNotFilter('term', [$this->field => $value]);
        return $this->query;
    }


    /**
     * @param $values - массив допустимых значений
     * @example $query->where('channel')->notIn([1,2,3]);
     * @return SearchQuery;
     */
    public function notIn(array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->query->addNotFilter('terms', [$this->field => $values]);
        return $this->query;
    }


    /**
     * @param $min
     * @param $max
     * @param null $dateFormat
     * @return SearchQuery
     */
    public function notBetween($min, $max, $dateFormat = null)
    {
        $params = ['gte' => $min, 'lte' => $max];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addNotFilter('range', [$this->field => $params]);
        return $this->query;
    }

    /**
     * @param $text
     * @return SearchQuery
     */
    public function notMatch($text)
    {
        if (is_array($this->field)) {
            $this->query->addNotFilter('multi_match', [
                'query' => $text,
                'fields' => $this->field
            ]);
        } else {
            $this->query->addNotFilter('match', [$this->field => $text]);
        }
        return $this->query;
    }

    /**
     * @return SearchQuery
     */
    public function notExists()
    {
        $this->query->addNotFilter('exists', ["field" => $this->field]);
        return $this->query;
    }

    /**
     * маска должна указываться в нижнем регистре, см. оф.документацию
     * Внимание! низкая производительность
     * @param $mask
     * @return SearchQuery
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
     */
    public function wildcard($mask)
    {
        $this->query->addFilter('wildcard', [$this->field => $mask], $this->context);
        return $this->query;
    }

    /**
     * Регулярное выражение должно указываться в нижнем регистре, см. оф.документацию
     * Внимание! низкая производительность
     * @param $regexp
     * @param $flags
     * @param $maxDeterminizedStates
     * @return SearchQuery
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
     */
    public function regexp($regexp, $flags = null, $maxDeterminizedStates = null)
    {
        $filter = ['value' => $regexp];

        if (null !== $flags) {
            $filter['flags'] = $flags;
        }

        if (null !== $maxDeterminizedStates) {
            $filter['max_determinized_states'] = $maxDeterminizedStates;
        }

        $this->query->addFilter('regexp', [$this->field => $filter], $this->context);
        return $this->query;
    }
}
