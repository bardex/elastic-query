<?php namespace Bardex\Elastic;

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
     * @var string разделитель строк в теле скрипта
     */
    protected $lineSeparator = "\n";

    /**
     * Создать новый скрипт
     * @param string $language =painless
     */
    public function __construct($language = 'painless')
    {
        $this->language = $language;
    }


    /**
     * Установить разделитель строк в теле скрипта
     * @param string $lineSeparator
     * @return self $this
     */
    public function setLineSeparator($lineSeparator)
    {
        $this->lineSeparator = $lineSeparator;
        return $this;
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
     * Скомпилировать скрипт в elastic-query тип
     * @return array
     */
    public function compile()
    {
        $script = [
            'script' => [
                'lang' => $this->getLanguage(),
                'inline' => $this->getBody(),
                'params' => $this->getParams()
            ]
        ];

        // because ES
        if (empty($script['script']['params'])) {
            unset($script['script']['params']);
        }

        return $script;
    }

    /**
     * Скомпилировать тело скрипта
     * @return string
     */
    public function getBody()
    {
        $body = implode($this->lineSeparator, $this->lines);
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
