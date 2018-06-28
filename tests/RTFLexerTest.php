<?php

class RTFLexerTest extends PHPUnit_Framework_TestCase {

    public function testDocumentFromFile() {
        $doc = RTFLexer::file('tests/sample/hello-world.rtf');
        $this->assertInstanceOf('RTFLex\tree\RTFDocument', $doc);
    }
}
