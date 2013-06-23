<?php
namespace Reflector\Reflection\Dummy;

use Reflector\Reflection\Dummy\DummyReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;

class DummyInterfaceReflection implements InterfaceReflectionInterface, DummyReflectionInterface
{
    /**
     * @var NamespaceReflectionInterface
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
     * Returns direct parent interface
     *
     * @return InterfaceReflectionInterface|null
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
     * Returns interfaces (this and every parent)
     *
     * @return array
     */
    public function getInterfaces()
    {
        return array();
    }

    /**
     * Returns the direct parent interfaces
     *
     * @return array
     */
    public function getParents()
    {
        // TODO: Implement getParents() method.
    }

    /**
     * Returns the interface parent iterator
     *
     * @return \Iterator
     */
    public function getParentIterator()
    {
        // TODO: Implement getParentIterator() method.
    }
}
