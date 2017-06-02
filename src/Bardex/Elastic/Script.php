<?php

namespace Bardex\Elastic;


class Script
{
    /**
     * @var string язык скрипта
     */
    protected $language = '';

    /**
     * @var array параметры скрипта
     */
    protected $params = [];

    /**
     * @var array тело скрипта
     */
    protected $lines = [];

    /**
     * Создать новый скрипт
     * @param string $language=painless
     */
    public function __construct($language='painless')
    {
        $this->language = $language;
    }

    /**
     * Добавить строку в тело скрипта
     * @param string $line - строка
     * @return self $this
     */
    public function addLine($line)
    {
        $this->lines[] = $line;
        return $this;
    }

    /**
     * Добавить параметр скрипта
     * @param string $name - имя параметра
     * @param mixed $value - значение параметра
     * @return self $this
     */
    public function addParam($name, $value)
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Скомпилировать тело скрипта
     * @return string
     */
    public function getBody()
    {
        $body = implode("\n", $this->lines);
        return $body;
    }

    /**
     * Получить параметры скрипта
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Получить язык скрипта
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

}