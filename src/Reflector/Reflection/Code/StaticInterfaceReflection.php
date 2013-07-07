<?php
namespace Reflector\Reflection\Code;

use Reflector\Iterator\InterfaceParentIterator;
use Reflector\Reflection\Code\StaticReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\ReflectionRegistry;

class StaticInterfaceReflection implements InterfaceReflectionInterface, StaticReflectionInterface
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
     * @var string|null
     */
    protected $line;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $parents;

    /**
     * Constructs new class reflection.
     *
     * @param \PHPParser_Node_Stmt_Interface $node
     * @param ReflectionRegistry             $registry
     */
    public function __construct(\PHPParser_Node_Stmt_Interface $node, ReflectionRegistry $registry)
    {
        list($nsName, $myName) = $registry::explodeItemName($node->name);

        $this->file       = $node->filename;
        $this->line       = $node->getLine();
        $this->namespace  = $registry->getNamespace($nsName);
        $this->name       = $myName;
        $this->parents    = array();

        foreach ($node->extends as $parent) {
            $this->interfaces[] = $registry->getInterface($parent->toString(), $this->file, $this->line);
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
        $parentIt = $this->getParentIterator();

        while ($parentIt->valid()) {
            if ($parentIt->current()->getName() === $parentName) {
                return true;
            }
        }

        return false;
    }
}
