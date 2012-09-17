<?php
namespace Reflector\Reflection\Dummy;

use Reflector\Reflection\DummyReflectionInterface;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;

class DummyClassReflection implements ClassReflectionInterface, DummyReflectionInterface
{
    /**
     * @var NamespaceReflectionInterface
     */
    protected $namespace;

    /**
     * Constructs new reflection
     *
     * @param NamespaceReflectionInterface $namespace
     * @param string                       $name
     */
    public function __construct( NamespaceReflectionInterface $namespace, $name )
    {
        $this->namespace = $namespace;
        $this->name      = $name;
    }

    /**
     * Exports a class
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
     * Returns the string representation of the object
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
    public function getFileName()
    {
        return null;
    }

    /**
     * Returns line number within definition file
     *
     * @return int|null
     */
    public function getStartLine()
    {
        return null;
    }

    /**
     * Returns containing namespace
     *
     * @return iReflectionNamespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Returns class name
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->name;
    }

    /**
     * Returns fully qualified class name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->namespace->getName() .'\\'. $this->name;
    }

    /**
     * Returns direct parent class
     *
     * @return iReflectionClass|null
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Checks, wheter class has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName )
    {
        return false;
    }

    /**
     * Returns class interfaces
     *
     * @return \Iterator
     */
    public function getInterfaceIterator()
    {
        return new \EmptyIterator();
    }

    /**
     * Checks, whether class implements given parent
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface( $interfaceName )
    {
        return false;
    }

    /**
     * Tells whether class is abstract
     *
     * @return bool
     */
    public function isAbstract()
    {
        return false;
    }

    /**
     * Tells whether class is final
     *
     * @return bool
     */
    public function isFinal()
    {
        return false;
    }
}
