<?php
namespace Reflector;

use Reflector\Reflection\NamespaceReflectionInterface;

class AliasResolver
{
	/**
	 * @var string
	 */
	protected $fileName;

	/**
	 * @var array
	 */
	protected $aliases;

	/**
	 * Constructs new NameResolver
	 *
	 * @param string $fileName
	 */
	public function __construct( $fileName = '' )
	{
		$this->fileName = $fileName;
		$this->aliases  = array();
	}

	/**
	 * Returns resolver fileName
	 *
	 * @return string
	 */
	public function getFileName()
	{
		return $this->fileName();
	}

	/**
	 * Parses aliases definition block
	 *
	 * @param  Tokenizer $t
	 * @return array
	 */
	public function parseAliases( $namespaceName, Tokenizer $t )
	{
		$t->expectToken( T_USE );
		$token = $t->getToken();

		if( isset( $this->aliases[ $namespaceName ] ) === false ) {
			$this->aliases[ $namespaceName ] = array();
		}

		do {
			$token = $t->nextToken();
			$token = $t->parseName( $fullName );
			
			if( $fullName[0] !== '\\' ) {
				$fullName = '\\'.$fullName;
			}

			if( $t->checkToken( T_AS ) ) {
				$token = $t->nextToken();
				$token = $t->parseName( $alias );
			} else {
				$alias = substr( strrchr( $fullName, '\\' ), 1 );
			}

			$this->aliases[ $namespaceName ][ $alias ] = $fullName;
			
		} while( $t->checkToken( ',' ) );

		$t->expectToken( ';' );

		return $t->nextToken();
	}

	/**
	 * Translates given name to fully specified name
	 *
	 * @param  ReflectionNamespace $fileName
	 * @param  string              $name
	 * @return string
	 */
	public function resolveName( NamespaceReflectionInterface $namespace, $name )
	{
		// fully classified name -> not an alias
		if( $name[0] === '\\' ) {
			return $name;
		}

		$namespaceName = $namespace->getName();

		// unknown alias / not an alias -> translate relative to fully classified name
		if( !isset( $this->aliases[ $namespaceName ], $this->aliases[ $namespaceName ][ $name ] ) ) {
			return $namespaceName .'\\'. $name;
		}

		// alias translation
		return $this->aliases[ $namespaceName ][ $name ];
	}
}