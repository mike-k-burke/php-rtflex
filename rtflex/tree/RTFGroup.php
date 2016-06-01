<?php

namespace RTFLex\tree;

use RTFLex\tokenizer\RTFToken;
use RTFLex\tokenizer\RTFTokenizer;


class RTFGroup
{

    /**
     * @var array
     */
    private $controls = array();

    /**
     * @var array
     */
    private $content = array();

    /**
     * @var
     */
    private $parent;

    /**
     * @param bool|false $allowInvisible
     * @param bool|true $newlinesAsSpaces
     * @return string
     */
    public function extractText($allowInvisible = false, $newlinesAsSpaces = true)
    {
        if (! $this->isPrintableText() && ! $allowInvisible) {
            return '';
        }

        $text = '';
        foreach ($this->content as $piece) {
            $text .= $piece->extractText($allowInvisible, $newlinesAsSpaces);
        }

        return $text;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasControlWord($name)
    {
        foreach ($this->controls as $control) {
            if ($control->getName() == $name) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPrintableText()
    {
        foreach ($this->controls as $control) {
            if (isset(RTFTokenizer::$nonPrintableWords[$control->getName()])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function listChildren()
    {
        $children = array();

        foreach ($this->content as $piece) {
            if ($piece instanceof RTFGroup) {
                $children[] = $piece;
            }
        }

        return $children;
    }

    /**
     * @param RTFToken $token
     * @throws \Exception
     */
    public function pushContent(RTFToken $token)
    {
        $type = $token->getType();
        if ($type != RTFToken::T_CONTROL_SYMBOL && $type != RTFToken::T_TEXT && $type != RTFToken::T_CONTROL_WORD) {
            throw new \Exception("Content must be either T_CONTROL_SYMBOL or T_TEXT or T_CONTROL_WORD");
        }

        $this->content[] = $token;
    }

    /**
     * @param RTFToken $token
     * @throws \Exception
     */
    public function pushControlWord(RTFToken $token)
    {
        if ($token->getType() != RTFToken::T_CONTROL_WORD) {
            throw new \Exception("Incorrect token type");
        }

        $this->controls[] = $token;
    }

    /**
     * @param RTFGroup $group
     */
    public function pushGroup(RTFGroup $group)
    {
        $group->setParent($this);
        $this->content[] = $group;
    }

    /**
     * @param RTFGroup $parent
     */
    protected function setParent(RTFGroup $parent)
    {
        $this->parent = $parent;
    }
}
