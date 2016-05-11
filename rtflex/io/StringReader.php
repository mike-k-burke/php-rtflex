<?php

namespace RTFLex\io;


class StringReader implements IByteReader
{
    private $index = 0;
    private $byteIndex = 0;
    private $string = null;
    private $lookAheadCache = null;
    private $cacheOffset = null;

    /**
     * StringReader constructor.
     * @param $string
     */
    public function __construct($string)
    {
        $this->string = $string;
        $this->lookAheadCache = null;
        $this->cacheOffset = null;
    }

    /**
     *
     */
    public function close()
    {
        $this->string = null;
        $this->index = 0;
        $this->byteIndex = 0;
    }

    /**
     * @param int $offset
     * @return bool|string
     */
    public function lookAhead($offset = 0)
    {
        if (is_null($this->lookAheadCache) || ($offset != $this->cacheOffset)) {
            $this->lookAheadCache = mb_substr($this->string, $this->index + $offset, 1);
            $this->cacheOffset = $offset;
        }
        return strlen($this->lookAheadCache) == 0 ? false : $this->lookAheadCache;
    }

    /**
     * @return bool|string
     */
    public function readByte()
    {
        $byte = $this->lookAhead();
        $this->index++;
        $this->byteIndex += mb_strlen($byte);
        $this->lookAheadCache = null;
        $this->cacheOffset = null;
        return $byte;
    }

    /**
     * @param $regexDelim
     * @return string
     */
    public function getToken($regexDelim)
    {
        $token = '';
        if(preg_match($regexDelim, $this->string, $matches, PREG_OFFSET_CAPTURE, $this->byteIndex)) {
            $token = substr($this->string, $this->byteIndex, $matches[0][1] - $this->byteIndex);
            $this->index += mb_strlen($token);
            $this->byteIndex = $matches[0][1];
            $this->lookAheadCache = null;
            $this->cacheOffset = null;
        };

        return $token;
    }
}
