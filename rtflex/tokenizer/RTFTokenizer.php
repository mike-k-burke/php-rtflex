<?php

namespace RTFLex\tokenizer;

use RTFLex\io\IByteReader;

class RTFTokenizer implements ITokenGenerator
{
    const CONTROL_CHARS = '/[\\\\|\{\}]/';
    const CONTROL_WORD = '/[^0-9\\\\\{\}\s\*\-]/s';
    const CONTROL_WORD_DELIM = '/[\?\;\ ]/';
    const NUMERIC = '/[\-0-9]/';
    const HEX = '/[\-0-9A-F]/i';
    const HEX_BYTE = '\'';

    // Put custom delimiter patterns here
    const COLORTBL_DELIM = '/[\;]/';

    /**
     * @var IByteReader
     */
    private $reader;

    /**
     * Map certain control words to custom delimiter patterns
     *
     * @var array
     */
    private $ctrlWordDelimMap = [
        'colortbl' => self::COLORTBL_DELIM,
        'red' => self::COLORTBL_DELIM,
        'green' => self::COLORTBL_DELIM,
        'blue' => self::COLORTBL_DELIM,
        'ctint' => self::COLORTBL_DELIM,
        'cshade' => self::COLORTBL_DELIM,
        'cmaindarkone' => self::COLORTBL_DELIM,
        'cmainlightone' => self::COLORTBL_DELIM,
        'cmaindarktwo' => self::COLORTBL_DELIM,
        'cmainlighttwo' => self::COLORTBL_DELIM,
        'caccentone' => self::COLORTBL_DELIM,
        'caccenttwo' => self::COLORTBL_DELIM,
        'caccentthree' => self::COLORTBL_DELIM,
        'caccentfour' => self::COLORTBL_DELIM,
        'caccentfive' => self::COLORTBL_DELIM,
        'caccentsix' => self::COLORTBL_DELIM,
        'chyperlink' => self::COLORTBL_DELIM,
        'cfollowedhyperlink' => self::COLORTBL_DELIM,
        'cbackgroundone' => self::COLORTBL_DELIM,
        'ctextone' => self::COLORTBL_DELIM,
        'ctextwo' => self::COLORTBL_DELIM,
    ];

    /**
     * @param IByteReader $reader
     */
    public function __construct(IByteReader $reader)
    {
        $this->reader = $reader;
    }

    private function getControlWordDelims($controlWord)
    {
        if (isset($this->ctrlWordDelimMap[$controlWord])) {
            return $this->ctrlWordDelimMap[$controlWord];
        }

        return self::CONTROL_WORD_DELIM;
    }

    /**
     * @return array
     */
    private function readControlWord()
    {
        $word = '';
        while (preg_match(self::CONTROL_WORD, $this->reader->lookAhead())) {
            $byte = $this->reader->readByte();
            $word .= $byte;
            if ($byte == ' ' || $byte == self::HEX_BYTE) {
                break;
            }
        }

        $param = '';
        $isHex = false;
        if (! empty($word)) {
            $isHex = ($word[0] == self::HEX_BYTE);
        }

        $paramEncoding = $isHex ? self::HEX : self::NUMERIC;
        while (preg_match($paramEncoding, $this->reader->lookAhead())) {
            $param .= $this->reader->readByte();
        }

        // Convert from hex?
        if ($isHex) {
            $param = hexdec($param);
        }

        // Swallow excess characters
        while (! preg_match($this->getControlWordDelims($word), $this->reader->lookAhead()) &&
            ! preg_match(self::CONTROL_CHARS, $this->reader->lookAhead())) {
            $this->reader->readByte();
        }

        // Swallow the control word delimiter
        if ((empty($param) && ! preg_match(self::CONTROL_CHARS, $this->reader->lookAhead())) ||
            preg_match($this->getControlWordDelims($word), $this->reader->lookAhead())) {
            $this->reader->readByte();
        }

        $param = $param === '' ? null : $param;
        $param = is_numeric($param) ? (int)$param : null;

        switch ($word) {
            case '\'':
                $type = RTFToken::T_CONTROL_SYMBOL;
                break;

            default:
                $type = RTFToken::T_CONTROL_WORD;
        }

        return array($type, $word, $param);
    }

    /**
     * @param $start
     * @return string
     */
    private function readText($start)
    {
        if ($start == '\\') {
            return $start;
        }

        $buffer = $start;

        while (true) {
            $n0 = $this->reader->lookAhead();
            if ($n0 === false) {
                break;
            } elseif (preg_match(self::CONTROL_CHARS, $n0)) {
                break;
            }

            $buffer .= $this->reader->readByte();
        }

        return $buffer;
    }

    /**
     * @return bool|RTFToken
     */
    public function readToken()
    {
        $byte = $this->reader->readByte();
        if ($byte === false) {
            return false;
        }

        switch ($byte) {
            case '{':
                return new RTFToken(RTFToken::T_START_GROUP);

            case '}':
                return new RTFToken(RTFToken::T_END_GROUP);

            case '\\':
                $byte = $this->reader->lookAhead();

                // Check for Control Symbol
                if (! ctype_alnum($byte) && $byte != '\'') {
                    $byte = $this->reader->readByte();
                    return new RTFToken(RTFToken::T_CONTROL_SYMBOL, $byte, null);
                } else {
                    list($type, $word, $param) = $this->readControlWord();
                    return new RTFToken($type, $word, $param);
                }

            default:
                $str = $this->readText($byte);
                if ((trim($str)) === '') {
                    return $this->readToken();
                }
                return new RTFToken(RTFToken::T_TEXT, null, $str);
        }
    }
}
