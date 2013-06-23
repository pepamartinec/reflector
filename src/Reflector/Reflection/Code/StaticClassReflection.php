<?php
namespace Reflector\Reflection\Code;

use Reflector\AliasResolver;
use Reflector\Iterator\ClassInterfaceIterator;
use Reflector\Iterator\ClassParentIterator;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\Code\StaticReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Tokenizer\Tokenizer;
use Reflector\Tokenizer\UnexpectedTokenException;

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
        $token      = $t->nextToken();

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
        return $this->startLine;
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
        return $this->_isAbstract;
    }

    /**
     * {@inheritdoc}
     */
    public function isFinal()
    {
        return $this->_isFinal;
    }
}
