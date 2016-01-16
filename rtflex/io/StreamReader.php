<?php

namespace RTFLex\io;


class StreamReader implements IByteReader {
    const MODE = 'r';

    private $index = 0;
    private $file;
    private $handle;
    private $size;

    /**
     * StreamReader constructor.
     * @param $file
     */
    public function __construct($file) {
        $this->file = $file;
        $this->handle = fopen($this->file, self::MODE);

        $stats = fstat($this->handle);
        $this->size = $stats['size'];
    }

    /**
     *  Close the handle
     */
    public function close() {
        fclose($this->handle);
    }

    /**
     * @param int $offset
     * @return bool|string
     */
    public function lookAhead($offset = 0) {
        fseek($this->handle, $this->index + $offset);
        $byte = fread($this->handle, 1);
        return strlen($byte) == 0 ? false : $byte;
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
