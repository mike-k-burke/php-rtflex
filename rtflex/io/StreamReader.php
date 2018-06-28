<?php

namespace RTFLex\io;


class StreamReader implements IByteReader
{
    const MODE = 'r';

    private $index = 0;
    private $file;
    private $handle;
    private $size;
    private $lookAheadCache = null;
    private $cacheIndex = 0;

    const CACHE_SIZE = 2000;

    /**
     * StreamReader constructor.
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->handle = fopen($this->file, self::MODE);

        $stats = fstat($this->handle);
        $this->size = $stats['size'];

        fseek($this->handle, $this->index);
        $this->lookAheadCache = preg_split('//u', fread($this->handle, self::CACHE_SIZE), self::CACHE_SIZE,
            PREG_SPLIT_NO_EMPTY);

        if (! isset($this->lookAheadCache[$this->cacheIndex])) {
            $this->lookAheadCache[$this->cacheIndex] = false;
        }
    }

    /**
     *  Close the handle
     */
    public function close()
    {
        fclose($this->handle);
    }

    /**
     * @param int $offset
     * @return bool|string
     */
    public function lookAheadOffset($offset = 0)
    {
        fseek($this->handle, $this->index + $offset);
        $char = fread($this->handle, 1);

        return strlen($char) == 0 ? false : $char;
    }

    /**
     * @return bool|string
     */
    public function lookAhead()
    {
        return $this->lookAheadCache[$this->cacheIndex];
    }

    /**
     * @return bool|string
     */
    public function readByte()
    {
        $byte = $this->lookAhead();
        $this->index++;
        $this->cacheIndex++;

        if ($this->cacheIndex >= self::CACHE_SIZE) {
            $this->cacheIndex = 0;
            fseek($this->handle, $this->index);
            $this->lookAheadCache = preg_split('//u', fread($this->handle, self::CACHE_SIZE), self::CACHE_SIZE,
                PREG_SPLIT_NO_EMPTY);
        }

        if (! isset($this->lookAheadCache[$this->cacheIndex])) {
            $this->lookAheadCache[$this->cacheIndex] = false;
        }

        return $byte;
    }

    /**
     * @param $regexDelim
     * @return string
     */
    public function getToken($regexDelim)
    {
        $buffer = '';

        while (($temp = $this->lookAhead()) !== false && ! preg_match($regexDelim, $temp)) {
            $buffer .= $this->readByte();
        }

        return $buffer;
    }

}
