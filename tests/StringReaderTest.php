<?php

include "../rtflex/RTFLexer.php";

use RTFLex\io\StringReader;


class StringReaderTest extends PHPUnit_Framework_TestCase {

    public function testReadByte() {
        $reader = new StringReader('Syn? A');

        $this->assertEquals('S', $reader->readByte());
        $this->assertEquals('y', $reader->readByte());
        $this->assertEquals('n', $reader->readByte());
        $this->assertEquals('?', $reader->readByte());
        $this->assertEquals(' ', $reader->readByte());
        $this->assertEquals('A', $reader->readByte());

        $reader->close();
    }

    public function testLookAhead() {
        $reader = new StringReader('Hello World');

        $this->assertEquals('H', $reader->lookAhead());
        $this->assertEquals('e', $reader->lookAhead(1));
        $this->assertEquals('H', $reader->readByte());

        $this->assertEquals('e', $reader->lookAhead());
        $this->assertEquals('e', $reader->lookAhead());
        $this->assertEquals('e', $reader->readByte());

        $this->assertEquals('l', $reader->lookAhead());
        $this->assertEquals('o', $reader->lookAhead(2));
        $this->assertEquals('l', $reader->readByte());

        $reader->close();
    }
}
