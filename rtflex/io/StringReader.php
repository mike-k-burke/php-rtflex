<?php

namespace RTFLex\io;


class StringReader implements IByteReader {
    private $index = 0;
    private $string = null;
    private $size;

    /**
     * StringReader constructor.
     * @param $string
     */
    public function __construct($string) {
        $this->string = $string;
        $this->size = mb_strlen($string);
    }

    /**
     *
     */
    public function close() {
        $this->string = null;
        $this->index = 0;
        $this->size = null;
    }

    /**
     * @param int $offset
     * @return bool|string
     */
    public function lookAhead($offset = 0) {
        $pos = $this->index + $offset;
        $byte = mb_substr($this->string, $pos, 1);
        return mb_strlen($byte) == 0 ? false : $byte;
    }

    /**
     * @return bool|string
     */
    public function readByte() {
        $byte = $this->lookAhead();
        $this->index++;
        return $byte;
    }
}
