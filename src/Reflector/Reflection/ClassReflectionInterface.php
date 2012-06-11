<?php
namespace Reflector\Reflection;

use Reflector\ReflectionInterface;

interface ClassReflectionInterface extends ReflectionInterface
{
	/**
	 * Returns definition file name
	 *
	 * @return string|null
	 */
	public function getDefinitionFile();

	/**
	 * Returns line number within definition file
	 *
	 * @return int|null
	 */
	public function getStartLine();

	/**
	 * Returns containing namespace
	 *
	 * @return iReflectionNamespace
	 */
	public function getNamespace();

	/**
	 * Returns class name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Returns fully qualified class name
	 *
	 * @return string
	 */
	public function getFullName();

	/**
	 * Returns direct parent class
	 *
	 * @return iReflectionClass|null
	 */
	public function getParent();

	/**
	 * Checks, wheter class has given parent
	 *
	 * @param  string $parentName
	 * @return bool
	 */
	public function hasParent( $parentName );

	/**
	 * Returns class interfaces
	 *
	 * @return \Iterator
	 */
	public function getInterfacesIterator();

	/**
	 * Checks, whether class implements given parent
	 *
	 * @param  string $interfaceName
	 * @return bool
	 */
	public function implementsInterface( $interfaceName );

	/**
	 * Tells whether class is abstract
	 *
	 * @return bool
	 */
	public function isAbstract();

	/**
	 * Tells whether class is final
	 *
	 * @return bool
	 */
	public function isFinal();
}