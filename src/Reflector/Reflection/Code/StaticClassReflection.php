<?php
namespace Reflector\Reflection\Code;

use Reflector\AliasResolver;
use Reflector\Tokenizer\Tokenizer;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Reflection\StaticReflectionInterface;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Iterator\ClassParentIterator;
use Reflector\Iterator\ClassInterfaceIterator;

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
    protected $startLine;

    /**
     * @var int
     */
    protected $endLine;

    /**
     * @var bool
     */
    protected $_isAbstract;

    /**
     * @var bool
     */
    protected $_isFinal;

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
     * Constructs new object reflection
     *
     * @param NamespaceReflectionInterface $namespace
     * @param Tokenizer                    $t
     * @param AliasResolver                $r
     *
     * @throws UnexpectedTokenException
     */
    public function __construct(NamespaceReflectionInterface $namespace, Tokenizer $t, AliasResolver $r)
    {
        $this->namespace = $namespace;
        $this->file      = $t->getFile();
        $this->startLine = $t->getLine();

        $token = $t->getToken();

        // abstract, final
        $this->_isAbstract = false;
        $this->_isFinal    = false;

        while ($t->checkToken(T_CLASS) === false) {
            switch ($token[0]) {
                case T_ABSTRACT:
                    $this->_isAbstract = true;
                    break;

                case T_FINAL:
                    $this->_isFinal = true;
                    break;

                default:
                    throw new UnexpectedTokenException($token);
            }

            $token = $t->nextToken();
        }

        $token = $t->nextToken();

        // name
        $t->expectToken(T_STRING);
        $this->name = $token[1];
        $token = $t->nextToken();

        // extends, implements
        $this->interfaces = array();
        $this->parent     = null;

        while ($t->checkToken('{') === false) {
            switch ($token[0]) {
                case T_EXTENDS:
                    $token = $t->nextToken();

                    $name  = null;
                    $token = $t->parseName($name);

                    $this->parent = $r->getClass($this->namespace, $name);
                    break;

                case T_IMPLEMENTS:
                    do {
                        $token = $t->nextToken();

                        $name  = null;
                        $token = $t->parseName($name);

                        $this->interfaces[] = $r->getInterface($this->namespace, $name);
                    } while ($token == ',');
                    break;

                default:
                    throw new UnexpectedTokenException($token);
            }
        }

        // parse body
        $t->parseBracketsBlock();

        $this->endLine = $t->getLine();
    }

    /**
     * Exports a class
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
     * Returns the string representation of the object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns definition file name
     *
     * @return string|null
     */
    public function getDefinitionFile()
    {
        return $this->file;
    }

    /**
     * Get the starting line number.
     *
     * @return int
     */
    public function getStartLine()
    {
        return $this->startLine;
    }

    /**
     * Gets end line number from a user-defined class definition.
     *
     * @return int
     */
    public function getEndLine()
    {
        return $this->endLine;
    }

    /**
     * Returns containing namespace
     *
     * @return ReflectionNamespaceInterface
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns fully qualified class name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->namespace->getName() . '\\' . $this->name;
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
        return $this->_isAbstract;
    }

    /**
     * Tells whether class is final
     *
     * @return bool
     */
    public function isFinal()
    {
        return $this->_isFinal;
    }
}
