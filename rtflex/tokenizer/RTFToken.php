<?php

namespace RTFLex\tokenizer;


class RTFToken {
    const T_START_GROUP = 1;
    const T_END_GROUP = 2;
    const T_CONTROL_WORD = 3;
    const T_CONTROL_SYMBOL = 4;
    const T_TEXT = 5;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $data;

    /**
     * @param $type
     * @param null $name
     * @param null $data
     */
    public function __construct($type, $name = null, $data = null) {
        $this->type = $type;
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @param bool|false $allowInvisible
     * @param bool|true $newlinesAsSpaces
     * @return null|string
     */
    public function extractText($allowInvisible = false, $newlinesAsSpaces = true) {
        if ($this->type == self::T_TEXT)  {
            return $this->data;
        }

        if ($this->type == self::T_CONTROL_WORD || $this->type == self::T_CONTROL_SYMBOL) {
            switch ($this->name) {
                case 'u':
                case 'u-':
                case "'":
                    return $this->uchr($this->data);

                case 'page':
                case 'par':
                case 'column':
                case 'line':
                case 'sect':
                case 'softpage':
                case 'softcol':
                case 'softline':
                    return ($newlinesAsSpaces ? ' ' : "\n");

                default:
                    break;
            }

            return "";
        }

        return "";
    }

    /**
     * @return null|string
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return null|string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * RTF uses 16-bit signed integers, which means unicode characters
     * above 32767 roll over into negative numbers. This converts then back into
     * 16-bit unsigned int's
     *
     * @param $code
     * @return string
     */
    protected function uchr($code) {
        $code = (int)$code;
        if ($code < 0) {
            $offset = pow(2, 15);
            $code = abs(-$offset - $code) + $offset;
        }

        return html_entity_decode("&#{$code};", ENT_NOQUOTES, 'UTF-8');
    }
}
