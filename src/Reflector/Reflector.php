<?php
namespace Reflector;

use Reflector\Reflection\DummyClassReflection;
use Reflector\Reflection\RuntimeClassReflection;
use Reflector\Reflection\DummyInterfaceReflection;
use Reflector\Reflection\RuntimeInterfaceReflection;
use Reflector\Reflection\FunctionReflectionInterface;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\StaticNamespaceReflection;
use Reflector\InvalidFileException;

class Reflector
{
	/**
	 * @var array
	 */
	protected $namespaces;

	/**
	 * @var array
	 */
	protected $analyzedFiles;

	/**
	 * Constructs new ReflectionFactory
	 */
	public function __construct()
	{
		$this->namespaces    = array();
		$this->analyzedFiles = array();
	}

	/**
	 * Analyzes whole directory
	 *
	 * @param string $dirName
	 * @param bool   $recursive
	 */
	public function analyzeDirectory( $dirName, $recursive = true )
	{
		if( is_dir( $dirName ) === false ) {
			throw new InvalidFileException( $dirName );
		}

		$iterator = null;
		if( $recursive ) {
			$iterator = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $dirName ) );
		} else {
			$iterator = new \DirectoryIterator( $dirName );
		}

		foreach( $iterator as $item ) {
			if( $item->isDir() ) {
				continue;
			}

			if( preg_match( '/\.php/i', $item->getFilename() ) == 0 ) {
				continue;
			}

			$this->analyzeFile( $item->getPathname() );
		}
	}

	/**
	 * Analyzes whole file and returns found namespaces
	 *
	 * @param string $fileName
	 *
	 * @throws InvalidFileException
	 * @throws InvalidSyntaxException
	 */
	public function analyzeFile( $fileName )
	{
		if( is_file( $fileName ) === false ) {
			throw new InvalidFileException( $fileName );
		}

		if( ! isset( $this->analyzedFiles[ $fileName ] ) ) {
			$aliasResolver = new AliasResolver( $fileName );
			$tokenizer     = Tokenizer::fromFile( $fileName );

			$this->analyzedFiles[ $fileName ] = $this->analyzeCode( $tokenizer, $aliasResolver );
		}

		$this->analyzedFiles[ $fileName ];
	}

	/**
	 * Analyzes given source code and returns found namespaces
	 *
	 * @param  Tokenizer     $t
	 * @param  AliasResolver $r
	 *
	 * @throws InvalidSyntaxException
	 */
	protected function analyzeCode( Tokenizer $t, AliasResolver $r )
	{
		$localNs = array();

		$withNamespace = $t->checkToken( T_OPEN_TAG, null, 0 ) && (
							( $t->checkToken( T_NAMESPACE, null, 1 ) ) ||
							( $t->checkToken( T_WHITESPACE, null, 1 ) && $t->checkToken( T_NAMESPACE, null, 2 ) )
		                 );

		try {
			// namespace specified
			// multiple namespaces may occur
			if( $withNamespace ) {
				// T_OPEN_TAG
				$t->expectToken( T_OPEN_TAG );
				$token = $t->nextToken();

				do {
					list( $name, $isBracketed ) = StaticNamespaceReflection::parseHead( $t );
					$namespace = $this->getNamespace( $name );
					$namespace->parseBody( $isBracketed, $t, $r );

					$localNs[ $name ] = $namespace;
				} while( $t->getToken() );

			// no namespace specified
			// whole code has to be in global namespace
			} else {
				// HTML before code
				if( $t->checkToken( T_INLINE_HTML ) ) {
					$token = $t->nextToken();
				}

				// T_OPEN_TAG
				$t->expectToken( T_OPEN_TAG );
				$token = $t->nextToken();

				$namespace = $this->getNamespace( '\\' );
				$namespace->parseBody( false, $t, $r );

				$localNs[ $name ] = $namespace;
			}

		} catch( UnexpectedTokenException $e ) {
			throw new InvalidSyntaxException( $e->getMessage(), null, $e );
		}
	}

	/**
	 * Returns namespace reflection
	 *
	 * @param string $name fully classified namespace name
	 * @return NamespaceReflectionInterface
	 */
	public function getNamespace( $name )
	{
		// namespace not defined yet, create new empty one
		if( isset( $this->namespaces[ $name ] ) === false ) {
			$this->namespaces[ $name ] = new StaticNamespaceReflection( $name, $this );
		}

		return $this->namespaces[ $name ];
	}

	/**
	 * Return namespaces iterator
	 *
	 * @return \Iterator
	 */
	public function getNamespacesIterator()
	{
		return new \ArrayIterator( $this->namespaces );
	}

	/**
	 * Returns reflection for given interface
	 *
	 * @param  string $name fully qualified interface name
	 * @return InterfaceReflectionInterface
	 *
	 * @throws InvalidSyntaxException
	 * @throws InvalidItemException
	 */
	public function getInterface( $name )
	{
		list( $nsName, $itName ) = Tokenizer::explodeName( $name );

		$namespace = $this->getNamespace( $nsName );

		if( ! $namespace->hasItem( $itName ) ) {
			if( interface_exists( $name, false ) ) {
				$reflection = new RuntimeInterfaceReflection( new \ReflectionClass( strtolower( $name ) ), $this );
			} else {
				$reflection = new DummyInterfaceReflection( $namespace, $itName );
			}

			$namespace->addItem( $reflection );
		}

		$item = $namespace->getItem( $itName );

		if( $item instanceof InterfaceReflectionInterface === false ) {
			throw new InvalidItemException( 'Requested item is not an interface reflection' );
		}

		return $item;
	}

	/**
	 * Returns reflection for given class
	 *
	 * @param  string $name fully qualified class name
	 * @return ClassReflectionInterface
	 *
	 * @throws InvalidSyntaxException
	 * @throws InvalidItemException
	 */
	public function getClass( $name )
	{
		list( $nsName, $clName ) = Tokenizer::explodeName( $name );

		$namespace = $this->getNamespace( $nsName );

		if( $namespace->hasItem( $clName ) === false ) {
			if( class_exists( $name, true ) ) {
				$reflection = new RuntimeClassReflection( new \ReflectionClass( strtolower( $name ) ), $this );
			} else {
				$reflection = new DummyClassReflection( $namespace, $clName );
			}

			$namespace->addItem( $reflection );
		}

		$item = $namespace->getItem( $clName );

		if( $item instanceof ClassReflectionInterface === false ) {
			throw new InvalidItemException( 'Requested item is not a class reflection' );
		}

		return $item;
	}
}