<?php
namespace Reflector\Reflection;

use Reflector\Tokenizer;
use Reflector\ReflectionFactory;

class RuntimeInterfaceReflection implements InterfaceReflectionInterface, RuntimeReflectionInterface
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

        list( $nsName, $itName ) = Tokenizer::explodeName( '\\'.$this->reflection->getName() );
        $this->namespace = $f->getNamespace( $nsName );

        $parentReflection = $this->reflection->getParentClass();
        $this->parent = $parentReflection === false ? null : new RuntimeReflectionInterface( $parentReflection, $f );

        if ($this->parent !== null) {
            $this->interfaces = $this->parent->getInterfaces();
            $this->interfaces[ $this->getName() ] = $this;

        } else {
            $this->interfaces = array( $this->getName() => $this );
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
     * Returns interface name
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflection->getShortName();
    }

    /**
     * Returns fully qualified interface name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->namespace->getName() .'\\'. $this->getName();
    }

    /**
     * Returns direct parent interface
     *
     * @return iReflectionInterface|null
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
        $iface = $this;
        while ( ( $parent = $iface->getParent() ) !== null ) {
            if( $parent->getName() === $parentName )

                return true;

            $iface = $parent;
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
        return $this->interfaces;
    }
}
