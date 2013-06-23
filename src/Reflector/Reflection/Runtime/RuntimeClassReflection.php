<?php
namespace Reflector\Reflection\Runtime;

use Reflector\Iterator\ClassInterfaceIterator;
use Reflector\Iterator\ClassParentIterator;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Reflection\Runtime\RuntimeReflectionInterface;
use Reflector\ReflectionFactory;
use Reflector\Tokenizer\Tokenizer;

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
        $this->parent     = $parentReflection === false ? null : new RuntimeClassReflection($parentReflection, $f);

        $this->interfaces = array();
        foreach ($this->reflection->getInterfaces() as $interfaceName => $interface) {
            $this->interfaces['\\' . $interfaceName] = new RuntimeInterfaceReflection($interface, $f);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->reflection->getFileName();
    }

    /**
     * {@inheritdoc}
     */
    public function getStartLine()
    {
        return $this->reflection->getStartLine();
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * {@inheritdoc}
     */
    public function getShortName()
    {
        return $this->reflection->getShortName();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->namespace->getName() . '\\' . $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentIterator()
    {
        return new ClassParentIterator($this);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaceIterator()
    {
        return new ClassInterfaceIterator($this);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isAbstract()
    {
        return $this->reflection->isAbstract();
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal()
    {
        return $this->reflection->isFinal();
    }
}
