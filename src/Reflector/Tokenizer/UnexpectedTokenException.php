<?php
namespace Reflector\Tokenizer;

use Reflector\ReflectorException;

class UnexpectedTokenException extends ReflectorException
{
    public function __construct($token, $expected = null, $file = null, $line = null)
    {
        $tokenStr = is_string($token) ? $token : token_name($token[0]);
        $msg = "Unexpected token {$tokenStr}";

        if ($expected !== null) {
            $expectedStr = is_string($expected) ? $expected : token_name($expected[0]);

            $msg .= ", {$expectedStr} expected";
        }

        if ($file !== null) {
            $msg .= " in file {$file}";

            if ($line !== null) {
                "::{$line}";
            }
        }

        parent::__construct($msg);
    }
}
