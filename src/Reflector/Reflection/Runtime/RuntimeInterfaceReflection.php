<?php
namespace Reflector\Reflection\Runtime;

use Reflector\Iterator\InterfaceParentIterator;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\ReflectionRegistry;
use Reflector\Reflection\Runtime\RuntimeReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;

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
     * @var array
     */
    protected $parents;

    /**
     * Constructs new reflection
     *
     * @param \ReflectionClass  $reflection
     * @param ReflectionRegistry $registry
     */
    public function __construct(\ReflectionClass $reflection, ReflectionRegistry $registry)
    {
        $this->reflection = $reflection;

        list($nsName, $myName) = $registry::explodeItemName($this->reflection->getName());
        $this->namespace       = $registry->getNamespace($nsName);

        $this->parents = array();
        foreach ($reflection->getInterfaces() as $parent) {
            $this->parents[] = new RuntimeInterfaceReflection($parent, $registry);
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
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentIterator()
    {
        $parentIterator = new InterfaceParentIterator($this);

        return new \RecursiveIteratorIterator($parentIterator, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent($parentName)
    {
        $iface = $this;
        while (($parent = $iface->getParent()) !== null) {
            if ($parent->getName() === $parentName) {
                return true;
            }

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
