<?php
namespace Reflector\Tokenizer;

class Tokenizer
{
	/**
	 * @var ArrayIterator
	 */
	protected $tokens;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * TODO
	 *
	 * @var int
	 */
	protected $line;

	/**
	 * Constructs new PHP tokenizer
	 *
	 * @param array $tokens tokens array
	 */
	protected function __construct( array $tokens )
	{
		$this->tokens = new \ArrayIterator( $tokens );
		$this->file   = 'unknown';
		$this->line   = null;
	}

	/**
	 * Creates new tokenizer from source code
	 *
	 * @param  string    $sourceCode
	 * @return Tokenizer
	 */
	public static function fromCode( $sourceCode )
	{
		$tokens = token_get_all( $sourceCode );

		return new self( $tokens );
	}

	/**
	 * Creates new tokenizer from given file
	 *
	 * @param string     $filePath
	 * @return Tokenizer
	 */
	public static function fromFile( $filePath )
	{
		$sourceCode = file_get_contents( $filePath );

		$tokenizer = self::fromCode( $sourceCode );
		$tokenizer->file = $filePath;

		return $tokenizer;
	}

	/**
	 * Returns source file
	 *
	 * @return int
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Returns current source line
	 *
	 * @return int
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * Checks token for given type and value
	 *
	 * @param int         $type
	 * @param string|null $value
	 * @param int         $offset
	 *
	 * @throws UnexpectedTokenException
	 */
	public function checkToken( $type, $value = null, $offset = 0 )
	{
		$token = $this->getToken( $offset );

		return $token !== false && (
				( is_string( $token ) && $token == $type ) ||
				( is_array( $token ) && $token[0] == $type && ( $value === null || $token[1] == $value ) )
			);
	}

	/**
	 * Checks token for valid type and value
	 *
	 * @param int         $type
	 * @param string|null $value
	 * @param int         $offset
	 *
	 * @throws UnexpectedTokenException
	 */
	public function expectToken( $type, $value = null, $offset = 0 )
	{
		$token = $this->getToken();

		if( $token === false ||
		    ( is_string( $token ) && $token != $type ) ||
		    ( is_array( $token ) && ( $token[0] != $type || ( $value !== null && $token[1] != $value ) ) )
		) {
				throw new UnexpectedTokenException( $token, $type );
		}
	}

	/**
	 * Picks next token, skipping white spaces and comments
	 *
	 * @return array
	 */
	public function nextToken()
	{
		do {
			$this->tokens->next();
			$token = $this->tokens->current();

//			if( is_array( $token ) && $token[0] === T_CLOSE_TAG ) {
//				$this->expectToken( T_INLINE_HTML );
//				$html = next( $this->tokens );
//
//				$this->expectToken( T_OPEN_TAG );
//				$token = next( $tokens );
//
//				$next = $this->tokens[ key( $this->tokens ) + 1 ];
//			}
		} while( is_array( $token ) && in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ) ) );

		return $token;
	}

	/**
	 * Picks token at given offset from current position
	 *
	 * @param  int   $offset
	 * @return array
	 */
	public function getToken( $offset = 0 )
	{
		$key = $this->tokens->key();

		// out of range
		if( $key === null )
			return null;

		return $this->tokens->offsetGet( $key + $offset );
	}

	/**
	 * Parses name (namespace, class, ...)
	 *
	 * @param array $tokens
	 */
	public function parseName( &$name )
	{
		$name = '';

		// ansolute namespace
		if( $this->checkToken( T_NS_SEPARATOR ) ) {
			$token = $this->tokens->current();
			$name .= $token[1];

			$token = $this->nextToken();

		// relative namespace / no namespace
		} else {
			$token = $this->tokens->current();
		}

		$this->expectToken( T_STRING );
		$name .= $token[1];

		do {
			$token = $this->nextToken();

			// name continues with other namespace part
			if( $this->checkToken( T_NS_SEPARATOR ) ) {
				$token = $this->nextToken();
				$this->expectToken( T_STRING );

				$name .= '\\' . $token[1];

			// name is complete
			} else {
				return $token;
			}
		} while( true );
	}

	/**
	 * Skips whole brackets block
	 *
	 * @param  Tokenizer $t
	 * @return array           token right after block
	 */
	public function parseBracketsBlock()
	{
		$this->expectToken( '{' );
		$bracketsCounter = 1;
		$token = $this->nextToken();

		while( $token && $bracketsCounter > 0 ) {
			if( is_string( $token ) ) {
				if( $token === '{' ) {
					++$bracketsCounter;
				} elseif( $token === '}' ) {
					--$bracketsCounter;
				}

			} else {
				if( $token[0] === T_STRING_VARNAME || $token[0] === T_CURLY_OPEN || $token[0] === T_DOLLAR_OPEN_CURLY_BRACES ) {
					++$bracketsCounter;
				}
			}

			$token = $this->nextToken();
		}

		return $token;
	}

	/**
	 * Explodes name on namespace and item name
	 *
	 * @param  string $name fully classified name
	 * @return array        array( $namespace, $item )
	 */
	public static function explodeName( $name )
	{
		// legacy code workaround
		// (code with no namespace)
		if( $name[0] !== '\\' ) {
			$name = '\\'.$name;
		}

		$item      = substr( strrchr( $name, '\\' ), 1 ) ?: null;
		$namespace = substr( $name, 0, -( strlen( $item ) + 1 ) ) ?: '\\';
		
		return array( $namespace, $item );
	}
}