<?php
namespace Reflector\Reflection\Code;

use Reflector\InvalidItemException;
use Reflector\Iterator\CallbackFilterIterator;
use Reflector\RedeclarationException;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\Code\StaticReflectionInterface;
use Reflector\Reflection\Dummy\DummyReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceItemInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Reflection\Runtime\RuntimeReflectionInterface;
use Reflector\ReflectionRegistry;

class StaticNamespaceReflection implements NamespaceReflectionInterface, StaticReflectionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var NamespaceReflectionInterface|null
     */
    protected $parent;

    /**
     * @var NamespaceItemInterface[]
     */
    protected $items;

    /**
     * Constructs new namespace reflection
     *
     * @param string             $name
     * @param ReflectionRegistry $registry
     */
    public function __construct($name, ReflectionRegistry $registry)
    {
        if ($name === $registry::GLOBAL_NAMESPACE) {
            $this->parent = null;
            $this->name   = $name;

        } else {
            list($parentName, $myName) = $registry::explodeItemName($name);

            $this->parent = $registry->getNamespace($parentName);
            $this->name   = $myName;
        }

        $this->items = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return ($this->parent ? $this->parent->getName() . '\\' : '') . $this->name;
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParent($parentName)
    {
        $class = $this;
        while (($parent = $class->getParent()) !== null) {
            if ($parent->getName() === $parentName) {
                return true;
            }

            $class = $parent;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(NamespaceItemInterface $item)
    {
        if ($item->getNamespace() !== $this) {
            throw new InvalidItemException('Item comes from other namespace');
        }

        if (isset($this->items[$item->getShortName()])) {
            $previous = $this->items[$item->getShortName()];

            $isReplaceable = false;
            $isReplaceable = $isReplaceable || ($previous instanceof DummyReflectionInterface && ($item instanceof RuntimeReflectionInterface || $item instanceof StaticReflectionInterface));
            $isReplaceable = $isReplaceable || ($previous instanceof RuntimeReflectionInterface && $item instanceof StaticReflectionInterface);

            if (!$isReplaceable) {
                throw new RedeclarationException(
                    "Namespace {$this->getShortName()} already contains {$item->getName()}, ".
                    "previously declared at {$previous->getFileName()}:{$previous->getStartLine()}"
                );
            }
        }

        $this->items[$item->getShortName()] = $item;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($itemName)
    {
        return isset($this->items[$itemName]);
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($itemName)
    {
        if (!isset($this->items[$itemName])) {
            return null;
        }

        return $this->items[$itemName];
    }

    /**
     * {@inheritdoc}
     */
    public function getClassIterator()
    {
        $iterator = new \ArrayIterator($this->items);
        $filter   = function ($current) {
            return $current instanceof ClassReflectionInterface;
        };

        return new CallbackFilterIterator($iterator, $filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getInterfaceIterator()
    {
        $iterator = new \ArrayIterator($this->items);
        $filter   = function ($current) {
            return $current instanceof InterfaceReflectionInterface;
        };

        return new CallbackFilterIterator($iterator, $filter);
    }

//    /**
//     * Returns namespace global functions iterator
//     *
//     * @return \Iterator
//     */
//    public function getFunctionIterator()
//    {
//        $iterator = new \ArrayIterator($this->items);
//        $filter   = function($current) {
//            return $current instanceof FunctionReflectionInterface;
//        };
//
//        return new CallbackFilterIterator($iterator, $filter);
//    }
}
