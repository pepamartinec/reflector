<?php
namespace Reflector\Reflection\Code;

use Reflector\Iterator\InterfaceParentIterator;

use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Reflection\StaticReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\UnexpectedTokenException;
use Reflector\ReflectionFactory;
use Reflector\Tokenizer\Tokenizer;
use Reflector\AliasResolver;

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
     * @param ReflectionFactory            $f
     * @param Tokenizer                    $t
     * @param AliasResolver                $r
     *
     * @throws UnexpectedTokenException
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
        $token = $t->nextToken();

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
     * Exports an interface
     *
     * @param mixed $argument
     * @param bool  $return
     *
     * @return string|null
     */
    public static function export($argument, $return = false)
    {

    }

    /**
     * @see Reflector::__toString()
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getDefinitionFile()
     */
    public function getDefinitionFile()
    {
        return $this->file;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getStartLine()
     */
    public function getStartLine()
    {
        return $this->line;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getNamespace()
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getName()
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getFullName()
     */
    public function getFullName()
    {
        return $this->namespace->getName() .'\\'. $this->name;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getParents()
     */
    public function getParents()
    {
       return $this->parents;
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getParentIterator()
     */
    public function getParentIterator()
    {
        $parentIterator = new InterfaceParentIterator($this);

        return new \RecursiveIteratorIterator($parentIterator, \RecursiveIteratorIterator::SELF_FIRST);
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::hasParent()
     */
    public function hasParent($parentName)
    {
        $parentIt = $this->getParentIterator();

        while ($parentIt->valid()) {
            if ($parentIt->current()->getFullName() === $parentName) {
                return true;
            }
        }

        return false;
    }
}
