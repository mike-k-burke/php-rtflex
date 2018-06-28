<?php

namespace RTFLex\tree;

use RTFLex\tokenizer\ITokenGenerator;
use RTFLex\tokenizer\RTFToken;
use RTFLex\tokenizer\RTFTokenizer;

class RTFDocument
{
    /**
     * @var array
     */
    private $groupStack = array();

    /**
     * @var RTFGroup
     */
    private $rootGroup;

    /**
     * @var RTFGroup
     */
    private $metadataGroup;


    /**
     * @param ITokenGenerator $tokenizer
     */
    public function __construct(ITokenGenerator $tokenizer)
    {
        $this->buildTree($tokenizer);
    }

    /**
     * @param ITokenGenerator $tokenizer
     * @throws \Exception
     */
    protected function buildTree(ITokenGenerator $tokenizer)
    {
        // Wipe the stack
        $this->groupStack = array();
        $this->rootGroup = null;

        do {
            $t = $tokenizer->readToken(! empty($this->groupStack));
            if ($t === null || $t === false) {
                continue;
            }
            $this->parseToken($t);
        } while ($t !== false);
    }

    /**
     * @param bool|false $allowInvisible
     * @param bool|true $newlinesAsSpaces
     * @return string
     */
    public function extractText($allowInvisible = false, $newlinesAsSpaces = true)
    {
        return $this->rootGroup->extractText($allowInvisible, $newlinesAsSpaces);
    }

    /**
     * @param RTFGroup $root
     * @param string $control
     * @return null
     */
    private function findGroup($root, $control)
    {
        if (! $root) {
            return null;
        }

        if ($root->hasControlWord($control)) {
            return $root;
        }

        foreach ($root->listChildren() as $child) {
            if ($group = $this->findGroup($child, $control)) {
                return $group;
            }
        }

        return null;
    }

    /**
     * @return null|RTFGroup
     */
    private function getInfoGroup()
    {
        if (is_null($this->metadataGroup)) {
            $this->metadataGroup = $this->findGroup($this->rootGroup, 'info');
        }
        return $this->metadataGroup;
    }

    /**
     * @param $name
     * @return null|string
     */
    public function getMetadata($name)
    {
        $info = $this->getInfoGroup();
        $block = $this->findGroup($info, $name);
        return $block instanceof RTFGroup
            ? trim($block->extractText($allowInvisible = true))
            : null;
    }

    /**
     * @param RTFToken $token
     * @throws \Exception
     */
    protected function parseToken($token)
    {
        switch ($token->getType()) {
            // Start a new Group
            case RTFToken::T_START_GROUP:
                $group = new RTFGroup();
                $parent = end($this->groupStack);
                if ($parent) {
                    $parent->pushGroup($group);
                } else {
                    $this->rootGroup = $group;
                }
                $this->groupStack[] = $group;
                break;

            // End the active group
            case RTFToken::T_END_GROUP:
                if (empty($this->groupStack)) {
                    throw new \Exception("Can not close group when open group doesn't exist");
                }
                array_pop($this->groupStack);
                break;

            // Attach a control word to the active group
            case RTFToken::T_CONTROL_WORD:
                if (empty($this->groupStack)) {
                    throw new \Exception("Can not use control word when open group doesn't exist");
                }
                $group = end($this->groupStack);

                if (isset(RTFTokenizer::$contentControlWords[$token->getName()])) {
                    $group->pushContent($token);
                } else {
                    $group->pushControlWord($token);
                }

                break;

            // Add content into the active group
            case RTFToken::T_CONTROL_SYMBOL:
            case RTFToken::T_TEXT:
                if (empty($this->groupStack)) {
                    throw new \Exception("Can not use content when open group doesn't exist");
                }
                $group = end($this->groupStack);
                $group->pushContent($token);
                break;
        }
    }
}
