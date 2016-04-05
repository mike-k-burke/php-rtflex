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
    private $cacheOffset = null;

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
        $this->lookAheadCache = null;
        $this->cacheOffset = null;
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
    public function lookAhead($offset = 0)
    {
        if (is_null($this->lookAheadCache) || ($offset != $this->cacheOffset)) {
            fseek($this->handle, $this->index + $offset);
            $this->lookAheadCache = fread($this->handle, 1);
            $this->cacheOffset = $offset;
        }
        return $this->lookAheadCache === '' ? false : $this->lookAheadCache;
    }

    /**
     * @return bool|string
     */
    public function readByte()
    {
        $byte = $this->lookAhead();
        $this->index++;
        $this->lookAheadCache = null;
        $this->cacheOffset = null;
        return $byte;
    }
}
