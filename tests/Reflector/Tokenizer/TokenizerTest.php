<?php
namespace Reflector\Tokenizer;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    public function testExplodeName()
    {
        $this->assertEquals(array('', ''), Tokenizer::explodeName(''));
        $this->assertEquals(array('', 'a'), Tokenizer::explodeName('a'));
        $this->assertEquals(array('', 'a'), Tokenizer::explodeName('\\a'));
        $this->assertEquals(array('x', 'a'), Tokenizer::explodeName('x\\a'));
        $this->assertEquals(array('x', 'a'), Tokenizer::explodeName('\\x\\a'));
        $this->assertEquals(array('x\\y', 'a'), Tokenizer::explodeName('x\\y\\a'));
        $this->assertEquals(array('x\\y', 'a'), Tokenizer::explodeName('\\x\\y\\a'));
    }
}