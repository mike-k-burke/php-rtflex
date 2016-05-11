<?php

namespace RTFLex\tokenizer;

use RTFLex\io\IByteReader;

class RTFTokenizer implements ITokenGenerator
{
    const CONTROL_CHARS = '/[\\\\|\{\}]/';
    const CONTROL_WORD = '/[^0-9\\\\\{\}\s\*\-]/s';
    const CONTROL_WORD_DELIM = '/[\?\;\ ]/';
    const CONTROL_WORD_TOKEN = '/[0-9\\\\\{\}\s\*\-\ \']/s';
    const HEX_TOKEN = '/[^\-0-9A-F]/i';
    const NUMERIC_TOKEN = '/[^\-0-9]/';
    const HEX_BYTE = '\'';

    /**
     * @var IByteReader
     */
    private $reader;

    /**
     * @param IByteReader $reader
     */
    public function __construct(IByteReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return array
     */
    private function readControlWord()
    {
        $word = $this->reader->getToken(self::CONTROL_WORD_TOKEN);

        if($this->reader->lookAhead() == self::HEX_BYTE) {
            $word .= $this->reader->readByte();
        }
        
        $isHex = false;
        if (! empty($word)) {
            $isHex = ($word[0] == self::HEX_BYTE);
        }

        $paramEncoding = $isHex ? self::HEX_TOKEN : self::NUMERIC_TOKEN;

        $param = $this->reader->getToken($paramEncoding);

        // Convert from hex?
        if ($isHex) {
            $param = hexdec($param);
        }

        // Swallow the control word delimiter
        if ((empty($param) && ! preg_match(self::CONTROL_CHARS, $this->reader->lookAhead())) ||
            preg_match(self::CONTROL_WORD_DELIM, $this->reader->lookAhead())
        ) {
            $this->reader->readByte();
        }

        $param = $param === '' ? null : $param;
        $param = is_numeric($param) ? (int)$param : null;

        switch ($word) {
            case 'u':
            case 'u-':
            case self::HEX_BYTE:
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

        return $start . $this->reader->getToken(self::CONTROL_CHARS);
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
                
                if ($byte == "\n") {
                    // Catch newlines
                    return new RTFToken(RTFToken::T_TEXT, null, $this->reader->readByte());
                }
                elseif (! ctype_alnum($byte) && $byte != self::HEX_BYTE) {
                    // Check for Control Symbol
                    return new RTFToken(RTFToken::T_CONTROL_SYMBOL, $this->reader->readByte(), null);
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
