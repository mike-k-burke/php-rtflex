<?php

namespace RTFLex\tokenizer;
use RTFLex\io\IByteReader;


class RTFTokenizer implements ITokenGenerator {
    const CONTROL_CHARS = "/[\\\\|\{\}]/";
    const CONTROL_WORD = "/[^0-9\\\\\{\}\s\*\-]/s";
    const CONTROL_WORD_DELIM = "/[\?\;]/";
    const NUMERIC = "/[\-0-9]/";
    const HEX = "/[\-0-9A-F]/i";
    const HEX_BYTE = "'";

    /**
     * @var IByteReader
     */
    private $reader;

    /**
     * @param IByteReader $reader
     */
    public function __construct(IByteReader $reader) {
        $this->reader = $reader;
    }

    /**
     * @return array
     */
    private function readControlWord() {
        if ($this->reader->lookAhead() == "\n") {
            return array(RTFToken::T_TEXT, null, $this->reader->readByte());
        }

        $word = "";
        while (preg_match(self::CONTROL_WORD, $this->reader->lookAhead())) {
            $byte = $this->reader->readByte();
            $word .= $byte;
            if ($byte == ' ' || $byte == self::HEX_BYTE) {
                break;
            }
        }

        $param = "";
        $isHex = strpos($word, self::HEX_BYTE) === 0;
        $paramEncoding = $isHex ? self::HEX : self::NUMERIC;
        while (preg_match($paramEncoding, $this->reader->lookAhead())) {
            $param .= $this->reader->readByte();
        }

        // Convert from hex?
        if ($isHex) {
            $param = hexdec($param);
        }

        // Swallow the control word delim
        $swallow = empty($param) && !preg_match(self::CONTROL_CHARS, $this->reader->lookAhead());
        $swallow = $swallow || preg_match(self::CONTROL_WORD_DELIM, $this->reader->lookAhead());
        if ($swallow) {
            $this->reader->readByte();
        }

        $param = strlen($param) == 0 ? null : $param;
        $param = is_numeric($param) ? (int)$param : null;
        $type = strlen($word) > 1 ? RTFToken::T_CONTROL_WORD : RTFToken::T_CONTROL_SYMBOL;

        // These are special cases to catch multi-character control symbols
        switch($word) {
            case 'bullet':
            case 'cell':
            case 'chatn':
            case 'chdate':
            case 'chdpa':
            case 'chdpl':
            case 'chftn':
            case 'chftnsep':
            case 'chftnsepc':
            case 'chpgn':
            case 'chtime':
            case 'column':
            case 'emdash':
            case 'emspace':
            case 'endash':
            case 'enspace':
            case 'lbrN ***':
            case 'ldblquote':
            case 'line':
            case 'lquote':
            case 'ltrmark':
            case 'nestcell ***':
            case 'nestrow ***':
            case 'page':
            case 'par':
            case 'qmspace *':
            case 'rdblquote':
            case 'row':
            case 'rquote':
            case 'rtlmark':
            case 'sect':
            case 'sectnum':
            case 'tab':
            case 'zwbo *':
            case 'zwj':
            case 'zwnbo *':
            case 'zwnj':
                $type = RTFToken::T_CONTROL_SYMBOL;
                break;
            default:
                break;
        }

        return array($type, $word, $param);
    }

    /**
     * @param $start
     * @return string
     */
    private function readText($start) {
        $buffer = $start;
        $last = $start;

        while (true) {
            $n0 = $this->reader->lookAhead();
            if ($n0 === false) {
                break;
            }

            if (preg_match(self::CONTROL_CHARS, $n0) && $last != "\\") {
                break;
            }

            $buffer .= $this->reader->readByte();
        }

        return $buffer;
    }

    /**
     * @return bool|RTFToken
     */
    public function readToken() {
        $byte = $this->reader->readByte();
        if ($byte === false) {
            return false;
        }

        switch ($byte) {
            case "{":
                return new RTFToken(RTFToken::T_START_GROUP);

            case "}":
                return new RTFToken(RTFToken::T_END_GROUP);

            case "\\":
                list($type, $word, $param) = $this->readControlWord();
                return new RTFToken($type, $word, $param);

            default:
                $str = $this->readText($byte);
                if (strlen((trim($str))) === 0) {
                    return $this->readToken();
                }
                return new RTFToken(RTFToken::T_TEXT, null, $str);
        }
    }
}
