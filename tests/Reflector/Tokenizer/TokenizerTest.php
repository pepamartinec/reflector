<?php
namespace Reflector\Tokenizer;

class TokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider explodeNameData
     */
    public function testExplodeName($name, $ns, $class)
    {
        $this->assertEquals(array($ns, $class), Tokenizer::explodeName($name));
    }

    public function explodeNameData()
    {
        return array(
            array('',          '',     ''),
            array('A',         '',     'A'),
            array('\\A',       '',     'A'),
            array('x\\A',      'x',    'A'),
            array('\\x\\A',    'x',    'A'),
            array('x\\y\\A',   'x\\y', 'A'),
            array('\\x\\y\\A', 'x\\y', 'A'),
        );
    }
}