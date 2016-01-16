<?php

namespace RTFLex\io;


interface IByteReader {

    /**
     * @return mixed
     */
    public function close();

    /**
     * @param int $offset
     * @return mixed
     */
    public function lookAhead($offset = 0);

    /**
     * @return mixed
     */
    public function readByte();
}
