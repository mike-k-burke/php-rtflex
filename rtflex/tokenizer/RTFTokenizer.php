<?php

namespace RTFLex\tokenizer;

use RTFLex\io\IByteReader;

class RTFTokenizer implements ITokenGenerator
{
    const CONTROL_CHARS = '/[\\\\|\{\}]/';
    const CONTROL_WORD = '/[^0-9\\\\\{\}\s\*\-]/s';
    const CONTROL_WORD_DELIM = '/[\?\;\ ]/';
    const CONTROL_WORD_TOKEN = '/[0-9\\\\\{\}\s\*\-\ \\\']/s';
    const HEX_TOKEN = '/[^\-0-9A-F]/i';
    const NUMERIC_TOKEN = '/[^\-0-9]/';
    const HEX_BYTE = '\'';

    // These control words define a text group that has a non-printable destination
    static $nonPrintableWords = array(
        'aftncn' => '',
        'aftnsep' => '',
        'aftnsepc' => '',
        'annotation' => '',
        'atnauthor' => '',
        'atndate' => '',
        'atnicn' => '',
        'atnid' => '',
        'atnparent' => '',
        'atnref' => '',
        'atntime' => '',
        'atrfend' => '',
        'atrfstart' => '',
        'author' => '',
        'background' => '',
        'bkmkend' => '',
        'bkmkstart' => '',
        'blipuid' => '',
        'buptim' => '',
        'category' => '',
        'colorschememapping' => '',
        'colortbl' => '',
        'comment' => '',
        'company' => '',
        'creatim' => '',
        'datafield' => '',
        'datastore' => '',
        'defchp' => '',
        'defpap' => '',
        'do' => '',
        'doccomm' => '',
        'docvar' => '',
        'dptxbxtext' => '',
        'ebcend' => '',
        'ebcstart' => '',
        'factoidname' => '',
        'falt' => '',
        'fchars' => '',
        'ffdeftext' => '',
        'ffentrymcr' => '',
        'ffexitmcr' => '',
        'ffformat' => '',
        'ffhelptext' => '',
        'ffl' => '',
        'ffname' => '',
        'ffstattext' => '',
        'field' => '',
        'file' => '',
        'filetbl' => '',
        'fldinst' => '',
        'fldrslt' => '',
        'fldtype' => '',
        'fname' => '',
        'fontemb' => '',
        'fontfile' => '',
        'fonttbl' => '',
        'footer' => '',
        'footerf' => '',
        'footerl' => '',
        'footerr' => '',
        'footnote' => '',
        'formfield' => '',
        'ftncn' => '',
        'ftnsep' => '',
        'ftnsepc' => '',
        'g' => '',
        'generator' => '',
        'gridtbl' => '',
        'header' => '',
        'headerf' => '',
        'headerl' => '',
        'headerr' => '',
        'hl' => '',
        'hlfr' => '',
        'hlinkbase' => '',
        'hlloc' => '',
        'hlsrc' => '',
        'hsv' => '',
        'htmltag' => '',
        'info' => '',
        'keycode' => '',
        'keywords' => '',
        'latentstyles' => '',
        'lchars' => '',
        'levelnumbers' => '',
        'leveltext' => '',
        'lfolevel' => '',
        'linkval' => '',
        'list' => '',
        'listlevel' => '',
        'listname' => '',
        'listoverride' => '',
        'listoverridetable' => '',
        'listpicture' => '',
        'liststylename' => '',
        'listtable' => '',
        'listtext' => '',
        'lsdlockedexcept' => '',
        'macc' => '',
        'maccPr' => '',
        'mailmerge' => '',
        'maln' => '',
        'malnScr' => '',
        'manager' => '',
        'margPr' => '',
        'mbar' => '',
        'mbarPr' => '',
        'mbaseJc' => '',
        'mbegChr' => '',
        'mborderBox' => '',
        'mborderBoxPr' => '',
        'mbox' => '',
        'mboxPr' => '',
        'mchr' => '',
        'mcount' => '',
        'mctrlPr' => '',
        'md' => '',
        'mdeg' => '',
        'mdegHide' => '',
        'mden' => '',
        'mdiff' => '',
        'mdPr' => '',
        'me' => '',
        'mendChr' => '',
        'meqArr' => '',
        'meqArrPr' => '',
        'mf' => '',
        'mfName' => '',
        'mfPr' => '',
        'mfunc' => '',
        'mfuncPr' => '',
        'mgroupChr' => '',
        'mgroupChrPr' => '',
        'mgrow' => '',
        'mhideBot' => '',
        'mhideLeft' => '',
        'mhideRight' => '',
        'mhideTop' => '',
        'mhtmltag' => '',
        'mlim' => '',
        'mlimloc' => '',
        'mlimlow' => '',
        'mlimlowPr' => '',
        'mlimupp' => '',
        'mlimuppPr' => '',
        'mm' => '',
        'mmaddfieldname' => '',
        'mmath' => '',
        'mmathPict' => '',
        'mmathPr' => '',
        'mmaxdist' => '',
        'mmc' => '',
        'mmcJc' => '',
        'mmconnectstr' => '',
        'mmconnectstrdata' => '',
        'mmcPr' => '',
        'mmcs' => '',
        'mmdatasource' => '',
        'mmheadersource' => '',
        'mmmailsubject' => '',
        'mmodso' => '',
        'mmodsofilter' => '',
        'mmodsofldmpdata' => '',
        'mmodsomappedname' => '',
        'mmodsoname' => '',
        'mmodsorecipdata' => '',
        'mmodsosort' => '',
        'mmodsosrc' => '',
        'mmodsotable' => '',
        'mmodsoudl' => '',
        'mmodsoudldata' => '',
        'mmodsouniquetag' => '',
        'mmPr' => '',
        'mmquery' => '',
        'mmr' => '',
        'mnary' => '',
        'mnaryPr' => '',
        'mnoBreak' => '',
        'mnum' => '',
        'mobjDist' => '',
        'moMath' => '',
        'moMathPara' => '',
        'moMathParaPr' => '',
        'mopEmu' => '',
        'mphant' => '',
        'mphantPr' => '',
        'mplcHide' => '',
        'mpos' => '',
        'mr' => '',
        'mrad' => '',
        'mradPr' => '',
        'mrPr' => '',
        'msepChr' => '',
        'mshow' => '',
        'mshp' => '',
        'msPre' => '',
        'msPrePr' => '',
        'msSub' => '',
        'msSubPr' => '',
        'msSubSup' => '',
        'msSubSupPr' => '',
        'msSup' => '',
        'msSupPr' => '',
        'mstrikeBLTR' => '',
        'mstrikeH' => '',
        'mstrikeTLBR' => '',
        'mstrikeV' => '',
        'msub' => '',
        'msubHide' => '',
        'msup' => '',
        'msupHide' => '',
        'mtransp' => '',
        'mtype' => '',
        'mvertJc' => '',
        'mvfmf' => '',
        'mvfml' => '',
        'mvtof' => '',
        'mvtol' => '',
        'mzeroAsc' => '',
        'mzeroDesc' => '',
        'mzeroWid' => '',
        'nesttableprops' => '',
        'nextfile' => '',
        'nonesttables' => '',
        'objalias' => '',
        'objclass' => '',
        'objdata' => '',
        'object' => '',
        'objname' => '',
        'objsect' => '',
        'objtime' => '',
        'oldcprops' => '',
        'oldpprops' => '',
        'oldsprops' => '',
        'oldtprops' => '',
        'oleclsid' => '',
        'operator' => '',
        'panose' => '',
        'password' => '',
        'passwordhash' => '',
        'pgp' => '',
        'pgptbl' => '',
        'picprop' => '',
        'pict' => '',
        'pn' => '',
        'pnseclvl' => '',
        'pntext' => '',
        'pntxta' => '',
        'pntxtb' => '',
        'printim' => '',
        'private' => '',
        'propname' => '',
        'protend' => '',
        'protstart' => '',
        'protusertbl' => '',
        'pxe' => '',
        'result' => '',
        'revtbl' => '',
        'revtim' => '',
        'rsidtbl' => '',
        'rxe' => '',
        'shp' => '',
        'shpgrp' => '',
        'shpinst' => '',
        'shppict' => '',
        'shprslt' => '',
        'shptxt' => '',
        'sn' => '',
        'sp' => '',
        'staticval' => '',
        'stylesheet' => '',
        'subject' => '',
        'sv' => '',
        'svb' => '',
        'tc' => '',
        'template' => '',
        'themedata' => '',
        'title' => '',
        'txe' => '',
        'ud' => '',
        'upr' => '',
        'userprops' => '',
        'wgrffmtfilter' => '',
        'windowcaption' => '',
        'writereservation' => '',
        'writereservhash' => '',
        'xe' => '',
        'xform' => '',
        'xmlattrname' => '',
        'xmlattrvalue' => '',
        'xmlclose' => '',
        'xmlname' => '',
        'xmlnstbl' => '',
        'xmlopen' => ''
    );

    static $contentControlWords = array(
        'page' => '',
        'par' => '',
        'column' => '',
        'line' => '',
        'sect' => '',
        'softpage' => '',
        'softcol' => '',
        'softline' => '',
        'bullet' => '',
        'cell' => '',
        'chatn' => '',
        'chdate' => '',
        'chdpa' => '',
        'chdpl' => '',
        'chftn' => '',
        'chftnsep' => '',
        'chftnsepc' => '',
        'chpgn' => '',
        'chtime' => '',
        'emdash' => '',
        'emspace' => '',
        'endash' => '',
        'enspace' => '',
        'lbrN ***' => '',
        'ldblquote' => '',
        'lquote' => '',
        'ltrmark' => '',
        'nestcell ***' => '',
        'nestrow ***' => '',
        'qmspace *' => '',
        'rdblquote' => '',
        'row' => '',
        'rquote' => '',
        'rtlmark' => '',
        'sectnum' => '',
        'tab' => '',
        'zwbo *' => '',
        'zwj' => '',
        'zwnbo *' => '',
        'zwnj' => '',
    );

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

        if ($this->reader->lookAhead() == self::HEX_BYTE) {
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
     * @param bool $isGroupOpen
     * @return bool|mixed|RTFToken
     */
    public function readToken($isGroupOpen = false)
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
                } elseif (! ctype_alnum($byte) && $byte != self::HEX_BYTE) {
                    // Check for Control Symbol
                    return new RTFToken(RTFToken::T_CONTROL_SYMBOL, $this->reader->readByte(), null);
                } else {
                    list($type, $word, $param) = $this->readControlWord();
                    return new RTFToken($type, $word, $param);
                }

            default:
                $str = $this->readText($byte);
                if ((trim($str)) === '' && (! $isGroupOpen || $str === "\n")) {
                    // If a group isn't currently open, eat this whitespace instead
                    // of considering it content
                    return null;
                }
                return new RTFToken(RTFToken::T_TEXT, null, $str);
        }
    }
}
