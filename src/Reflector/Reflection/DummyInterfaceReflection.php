<?php
namespace Reflector\Reflection;

class DummyInterfaceReflection implements InterfaceReflectionInterface, DummyReflectionInterface
{
    /**
     * @var iReflectionNamespace
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $name;

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
     * Returns interface name
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->name;
    }

    /**
     * Returns fully qualified interface name
     *
     * @return string
     */
    public function getName()
    {
        return $this->namespace->getName() .'\\'. $this->name;
    }

    /**
     * Returns direct parent interface
     *
     * @return iReflectionInterface|null
     */
    public function getParent()
    {
        return null;
    }

    /**
     * Checks, wheter interface has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName )
    {
        return false;
    }

    /**
     * Returns interfaces (this and every parent)
     *
     * @return array
     */
    public function getInterfaces()
    {
        return array();
    }
}
