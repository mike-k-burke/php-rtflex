<?php

use RTFLex\io\StreamReader;
use RTFLex\tokenizer\RTFTokenizer;
use RTFLex\tree\RTFDocument;


class RTFLexer
{

    public static function file($filename)
    {
        $reader = new StreamReader($filename);
        $tokenizer = new RTFTokenizer($reader);
        $doc = new RTFDocument($tokenizer);
        return $doc;
    }
}
