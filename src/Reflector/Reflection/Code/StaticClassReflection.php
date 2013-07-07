<?php
namespace Reflector\Reflection\Code;

use Reflector\Iterator\ClassInterfaceIterator;
use Reflector\Iterator\ClassParentIterator;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\Code\StaticReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\ReflectionRegistry;

class StaticClassReflection implements ClassReflectionInterface, StaticReflectionInterface
{
    /**
     * @var NamespaceReflectionInterface
     */
    protected $namespace;

    /**
     * @var string|null
     */
    protected $file;

    /**
     * @var int
     */
    protected $line;

    /**
     * @var bool
     */
    protected $abstract;

    /**
     * @var bool
     */
    protected $final;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ClassReflectionInterface|null
     */
    protected $parent;

    /**
     * @var array
     */
    protected $interfaces;

    /**
     * Constructs new class reflection.
     *
     * @param \PHPParser_Node_Stmt_Class $node
     * @param ReflectionRegistry         $registry
     */
    public function __construct(\PHPParser_Node_Stmt_Class $node, ReflectionRegistry $registry)
    {
        list($nsName, $myName) = $registry::explodeItemName($node->namespacedName->toString());

        $this->file       = $node->filename;
        $this->line       = $node->getLine();
        $this->namespace  = $registry->getNamespace($nsName);
        $this->abstract   = $node->type & $node::MODIFIER_ABSTRACT;
        $this->final      = $node->type & $node::MODIFIER_FINAL;
        $this->name       = $myName;
        $this->parent     = null;
        $this->interfaces = array();

        if ($node->extends) {
            $this->parent = $registry->getClass($node->extends->toString(), $this->file, $this->line);
        }

        foreach ($node->implements as $interface) {
            $this->interfaces[] = $registry->getInterface($interface->toString(), $this->file, $this->line);
        }

        $this->namespace->addItem($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartLine()
    {
        return $this->line;
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
        return $this->abstract;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal()
    {
        return $this->final;
    }
}
