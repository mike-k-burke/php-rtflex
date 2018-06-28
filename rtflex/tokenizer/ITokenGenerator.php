<?php

namespace RTFLex\tokenizer;

interface ITokenGenerator
{

    /**
     * @param bool $isGroupOpen
     * @return mixed
     */
    public function readToken($isGroupOpen = false);
}
