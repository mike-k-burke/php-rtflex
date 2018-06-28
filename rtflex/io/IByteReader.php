<?php

namespace RTFLex\io;


interface IByteReader
{

    /**
     * @return mixed
     */
    public function close();

    /**
     * @param int $offset
     * @return mixed
     */
    public function lookAheadOffset($offset = 0);

    /**
     * @return mixed
     */
    public function lookAhead();

    /**
     * @return mixed
     */
    public function readByte();

    /**
     * @param $regexDelim
     * @return mixed
     */
    public function getToken($regexDelim);
}
