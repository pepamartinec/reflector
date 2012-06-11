<?php
namespace Reflector\Tokenizer;

use Reflector\ReflectorException;

class UnexpectedTokenException extends ReflectorException
{
	public function __construct( array $token, $expected = null )
	{
		$tokenStr = is_string( $token ) ? $token : token_name( $token[0] );
		$msg = "Unexpected token {$tokenStr}";

		if( $expected !== null )
			$msg .= ', '.token_name( $expected ).' expected';

		parent::__construct( $msg );
	}
}