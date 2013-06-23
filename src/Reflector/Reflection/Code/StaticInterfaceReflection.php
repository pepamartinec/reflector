<?php
namespace Reflector\Reflection\Code;

use Reflector\AliasResolver;
use Reflector\Iterator\InterfaceParentIterator;
use Reflector\Reflection\Code\StaticReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Tokenizer\Tokenizer;

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
     * Constructs new interface reflection
     *
     * @param NamespaceReflectionInterface $namespace
     * @param Tokenizer                    $t
     * @param AliasResolver                $r
     */
    public function __construct(NamespaceReflectionInterface $namespace, Tokenizer $t, AliasResolver $r)
    {
        $this->namespace = $namespace;
        $this->file      = $t->getFile();
        $this->line      = $t->getLine();

        // T_INTERFACE
        $t->expectToken(T_INTERFACE);
        $token = $t->nextToken();

        // name
        $t->expectToken(T_STRING);
        $this->name = $token[1];
        $token      = $t->nextToken();

        // T_EXTENDS
        $this->parents = array();

        if ($t->checkToken(T_EXTENDS)) {
            do {
                $token = $t->nextToken();

                $name  = null;
                $token = $t->parseName($name);

                $this->parents[] = $r->getInterface($this->namespace, $name);
            } while ($token == ',');
        }

        // parse body
        $t->parseBracketsBlock();
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
