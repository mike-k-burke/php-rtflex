<?php

namespace RTFLex\io;


class StringReader implements IByteReader
{
    private $index = 0;
    private $byteIndex = 0;
    private $string = null;
    private $lookAheadCache = null;
    private $cacheIndex = 0;

    const CACHE_SIZE = 2000;

    /**
     * StringReader constructor.
     * @param $string
     */
    public function __construct($string)
    {
        $this->string = $string;

        $this->lookAheadCache = preg_split('//u', mb_substr($this->string, $this->index, self::CACHE_SIZE),
            self::CACHE_SIZE, PREG_SPLIT_NO_EMPTY);

        if (! isset($this->lookAheadCache[$this->cacheIndex])) {
            $this->lookAheadCache[$this->cacheIndex] = false;
        }
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
    public function lookAheadOffset($offset = 0)
    {
        $char = mb_substr($this->string, $this->index + $offset, 1);

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
        $this->byteIndex += mb_strlen($byte);

        if ($this->cacheIndex >= self::CACHE_SIZE) {
            $this->cacheIndex = 0;
            $this->lookAheadCache = preg_split('//u', mb_substr($this->string, $this->index, self::CACHE_SIZE),
                self::CACHE_SIZE, PREG_SPLIT_NO_EMPTY);
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
        $token = '';
        if (preg_match($regexDelim, $this->string, $matches, PREG_OFFSET_CAPTURE, $this->byteIndex)) {
            $token = substr($this->string, $this->byteIndex, $matches[0][1] - $this->byteIndex);
            $this->index += mb_strlen($token);
            $this->byteIndex = $matches[0][1];
            $this->cacheIndex += mb_strlen($token);

            if ($this->cacheIndex >= self::CACHE_SIZE) {
                $this->cacheIndex = 0;
                $this->lookAheadCache = preg_split('//u', mb_substr($this->string, $this->index, self::CACHE_SIZE),
                    self::CACHE_SIZE, PREG_SPLIT_NO_EMPTY);
            }

            if (! isset($this->lookAheadCache[$this->cacheIndex])) {
                $this->lookAheadCache[$this->cacheIndex] = false;
            }
        };

        return $token;
    }
}
