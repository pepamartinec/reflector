<?php
namespace Reflector\Reflection;

use Reflector\UnexpectedTokenException;
use Reflector\ReflectionFactory;
use Reflector\Tokenizer;
use Reflector\AliasResolver;

class StaticInterfaceReflection implements InterfaceReflectionInterface, StaticReflectionInterface
{
	/**
	 * @var NamespaceReflectionInterface
	 */
	protected $namespace;

	/**
	 * @var string|null
	 */
	protected $file;

	/**
	 * @var string|null
	 */
	protected $line;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var iReflectionInterface|null
	 */
	protected $parent;

	/**
	 * Constructs new interface reflection
	 *
	 * @param NamespaceReflectionInterface $namespace
	 * @param ReflectionFactory            $f
	 * @param Tokenizer                    $t
	 * @param AliasResolver                $r
	 *
	 * @throws UnexpectedTokenException
	 */
	public function __construct( NamespaceReflectionInterface $namespace, ReflectionFactory $f, Tokenizer $t, AliasResolver $r )
	{
		$this->namespace = $namespace;
		$this->file      = $t->getFile();
		$this->line      = $t->getLine();

		// T_INTERFACE
		$t->expectToken( T_INTERFACE );
		$token = $t->nextToken();

		// name
		$t->expectToken( T_STRING );
		$this->name = $token[1];
		$token = $t->nextToken();
		
		if( $t->checkToken( T_EXTENDS ) ) {
			$token = $t->nextToken();
			$token = $t->parseName( $localName );
			$originalName = $r->resolveName( $this->namespace, $localName );

			$this->parent = $f->getInterface( $originalName );
		}

		// parse body
		$t->parseBracketsBlock();
	}

	/**
	 * Exports an interface
	 *
	 * @param mixed $argument
	 * @param bool  $return
	 *
	 * @return string|null
	 */
	public static function export( $argument, $return = false )
	{

	}

	/**
	 * Returns the string representation of the ReflectionInteface object
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}

	/**
	 * Returns definition file name
	 *
	 * @return string|null
	 */
	public function getDefinitionFile()
	{
		return $this->file;
	}

	/**
	 * Returns line number within definition file
	 *
	 * @return int|null
	 */
	public function getStartLine()
	{
		return $this->line;
	}

	/**
	 * Returns containing namespace
	 *
	 * @return NamespaceReflectionInterface|null
	 */
	public function getNamespace()
	{
		return $this->namespace;
	}

	/**
	 * Returns interface name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns fully qualified interface name
	 *
	 * @return string
	 */
	public function getFullName()
	{
		return $this->namespace->getName() .'\\'. $this->name;
	}

	/**
	 * Returns direct parent interface
	 *
	 * @return iReflectionInterface
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * Checks, wheter interface has given parent
	 *
	 * @param  string $parentName
	 * @return bool
	 */
	public function hasParent( $parentName )
	{
		$item = $this;
		while( ( $item = $item->parent ) !== null ) {
			if( $parent === $item || $parent === $item->name )
				return true;
		}

		return false;
	}

	/**
	 * Returns interfaces (this and every parent)
	 *
	 * @return array
	 */
	public function getInterfaces()
	{
		if( $this->parent !== null ) {
			$interfaces = $this->parent->getInterfaces();
			$interfaces[ $this->name ] = $this;

			return $interfaces;

		} else {
			return array( $this->name => $this );
		}
	}
}