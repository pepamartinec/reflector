<?php
namespace Reflector\Reflection\Runtime;

use Reflector\Tokenizer\Tokenizer;
use Reflector\Iterator\ClassParentIterator;
use Reflector\Iterator\ClassInterfaceIterator;
use Reflector\ReflectionFactory;
use Reflector\Reflection\RuntimeReflectionInterface;
use Reflector\Reflection\ClassReflectionInterface;

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
     * @var ClassReflectionInterface
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
    public function __construct(\ReflectionClass $reflection, ReflectionFactory $f)
    {
        $this->reflection = $reflection;

        list($nsName, $itName) = Tokenizer::explodeName($reflection->getName());
        $this->namespace = $f->getNamespace($nsName);

        $parentReflection = $this->reflection->getParentClass();
        $this->parent = $parentReflection === false ? null : new RuntimeClassReflection($parentReflection, $f);

        $this->interfaces = array();
        foreach ($this->reflection->getInterfaces() as $interfaceName => $interface) {
            $this->interfaces['\\'.$interfaceName] = new RuntimeInterfaceReflection($interface, $f);
        }
    }

    /**
     * Returns definition file name
     *
     * @return string|null
     */
    public function getFileName()
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
     * @return NamespaceReflectionInterface
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
    public function getShortName()
    {
        return $this->reflection->getShortName();
    }

    /**
     * Returns fully qualified class name
     *
     * @return string
     */
    public function getName()
    {
        return $this->namespace->getName() .'\\'. $this->getName();
    }

    /**
     * Returns direct parent class
     *
     * @return ClassReflectionInterface|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the class parent iterator
     *
     * @return ClassParentIterator
     */
    public function getParentIterator()
    {
        return new ClassParentIterator($this);
    }

    /**
     * Checks, whether the class has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent($parentName)
    {
        foreach ($this->getParentIterator() as $name => $parent) {
            if ($parentName === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * @see ClassReflectionInterface::getInterfaces()
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * Returns class interfaces
     *
     * @return ClassInterfaceIterator
     */
    public function getInterfaceIterator()
    {
        return new ClassInterfaceIterator($this);
    }

    /**
     * Checks, whether class implements given parent
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface($interfaceName)
    {
        foreach ($this->getInterfaceIterator as $name => $interface) {
            if ($interfaceName === $name) {
                return true;
            }
        }

        return false;
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
