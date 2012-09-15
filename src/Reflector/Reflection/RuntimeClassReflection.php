<?php
namespace Reflector\Reflection;

use Reflector\Tokenizer;
use Reflector\ReflectionFactory;

class RuntimeClassReflection implements ClassReflectionInterface, RuntimeReflectionInterface
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var NamespaceReflectionInterface
     */
    protected $namespace;

    /**
     * @var iReflectionClass
     */
    protected $parent;

    /**
     * @var array
     */
    protected $interfaces;

    /**
     * Constructs new reflection
     *
     * @param \ReflectionClass  $reflection
     * @param ReflectionFactory $f
     */
    public function __construct( \ReflectionClass $reflection, ReflectionFactory $f )
    {
        $this->reflection = $reflection;

        list( $nsName, $itName ) = Tokenizer::explodeName( $reflection->getName() );
        $this->namespace = $f->getNamespace( $nsName );

        $parentReflection = $this->reflection->getParentClass();
        $this->parent = $parentReflection === false ? null : new RuntimeClassReflection( $parentReflection, $f );

        $this->interfaces = array();
        foreach ( $this->reflection->getInterfaces() as $interfaceName => $interface ) {
            $this->interfaces[ '\\'.$interfaceName ] = new RuntimeInterfaceReflection( $interface, $f );
        }
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
        return $this->reflection->export( $argument, $return );
    }

    /**
     * Returns the string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->reflection->__toString();
    }

    /**
     * Returns definition file name
     *
     * @return string|null
     */
    public function getDefinitionFile()
    {
        return $this->reflection->getFileName();
    }

    /**
     * Returns line number within definition file
     *
     * @return int|null
     */
    public function getStartLine()
    {
        return $this->reflection->getStartLine();
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
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflection->getShortName();
    }

    /**
     * Returns fully qualified class name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->namespace->getName() .'\\'. $this->getName();
    }

    /**
     * Returns direct parent class
     *
     * @return iReflectionClass|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Checks, wheter class has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName )
    {
        $class = $this;
        while ( ( $parent = $class->getParent() ) !== null ) {
            if( $parent->getName() === $parentName )

                return true;

            $class = $parent;
        }

        return false;
    }

    /**
     * Returns class interfaces iterator
     *
     * @return \Iterator
     */
    public function getInterfacesIterator()
    {
        return new \ArrayIterator( $this->interfaces );
    }

    /**
     * Checks, whether class implements given parent
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface( $interfaceName )
    {
        return isset( $this->interfaces[ $interfaceName ] );
    }

    /**
     * Tells whether class is abstract
     *
     * @return bool
     */
    public function isAbstract()
    {
        return $this->reflection->isAbstract();
    }

    /**
     * Tells whether class is final
     *
     * @return bool
     */
    public function isFinal()
    {
        return $this->reflection->isFinal();
    }
}
