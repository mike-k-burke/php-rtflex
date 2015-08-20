<?php

namespace RTFLex\io;


class StringReader implements IByteReader {
    private $index = 0;
    private $string = null;
    private $size;

    public function __construct($string) {
        $this->string = $string;
        $this->size = strlen($string);
    }

    public function close() {
        $this->string = null;
        $this->index = 0;
        $this->size = null;
    }

    public function lookAhead($offset = 0) {
        $pos = $this->index + $offset;
        $byte = substr($this->string, $pos, 1);
        return strlen($byte) == 0 ? false : $byte;
    }

    public function readByte() {
        $byte = $this->lookAhead();
        $this->index++;
        return $byte;
    }
}
