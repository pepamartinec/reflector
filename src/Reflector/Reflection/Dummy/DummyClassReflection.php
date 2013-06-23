<?php
namespace Reflector\Reflection\Dummy;

use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\Dummy\DummyReflectionInterface;
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
    public function __construct(NamespaceReflectionInterface $namespace, $name)
    {
        $this->namespace = $namespace;
        $this->name      = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartLine()
    {
        return null;
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
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->namespace->getName() . '\\' . $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent($parentName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaceIterator()
    {
        return new \EmptyIterator();
    }

    /**
     * {@inheritdoc}
     */
    public function implementsInterface($interfaceName)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAbstract()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentIterator()
    {
        // TODO: Implement getParentIterator() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaces()
    {
        // TODO: Implement getInterfaces() method.
    }
}
