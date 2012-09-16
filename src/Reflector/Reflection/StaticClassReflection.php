<?php
namespace Reflector\Reflection;

use Reflector\UnexpectedTokenException;
use Reflector\ReflectionFactory;
use Reflector\Tokenizer;
use Reflector\AliasResolver;

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
     * @var string|null
     */
    protected $line;

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
     * @param ReflectionNamespace $namespace
     * @param ReflectionFactory   $f
     * @param Tokenizer           $t
     * @param AliasResolver       $r
     *
     * @throws UnexpectedTokenException
     */
    public function __construct( NamespaceReflectionInterface $namespace, ReflectionFactory $f, Tokenizer $t, AliasResolver $r )
    {
        $this->namespace = $namespace;
        $this->file      = $t->getFile();
        $this->line      = $t->getLine();

        $token = $t->getToken();

        // abstract, final
        $this->_isAbstract = false;
        $this->_isFinal    = false;
        while ( $t->checkToken( T_CLASS ) === false ) {
            switch ($token[0]) {
                case T_ABSTRACT: $this->_isAbstract = true; break;
                case T_FINAL:    $this->_isFinal = true;    break;
                default: throw new UnexpectedTokenException( $token );
            }

            $token = $t->nextToken();
        }

        $token = $t->nextToken();

        // name
        $t->expectToken( T_STRING );
        $this->name = $token[1];
        $token = $t->nextToken();

        // extends, implements
        $this->interfaces = array();
        $this->parent     = null;
        while ( $t->checkToken( '{' ) === false ) {
            switch ($token[0]) {
                case T_EXTENDS:
                    $token = $t->nextToken();
                    $token = $t->parseName( $localName );
                    $originalName = $r->resolveName( $this->namespace, $localName );

                    $this->parent = $f->getClass( $originalName );
                    break;

                case T_IMPLEMENTS:
                    do {
                        $token = $t->nextToken();
                        $token = $t->parseName( $localName );
                        $originalName = $r->resolveName( $this->namespace, $localName );

                        $this->interfaces[ $originalName ] = $f->getInterface( $originalName );
                    } while ( $token == ',' );
                    break;

                default:
                    throw new UnexpectedTokenException( $token );
            }
        }

        // parse body
        $t->parseBracketsBlock();
    }

    /**
     * Exports a class
     *
     * @param mixed $argument
     * @param bool  $return
     *
     * @return string|null
     */
    public static function export( $argument, $return = false )
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
    public function getFileName()
    {
        return $this->file;
    }

    /**
     * Returns line number within definition file
     *
     * @return int|null
     */
    public function getStartLine()
    {
        return $this->line;
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
        return $this->namespace->getName() .'\\'. $this->name;
    }

    /**
     * Returns direct parent class
     *
     * @return iReflectionClass|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Checks, wheter class has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName )
    {
        $class = $this;
        while ( ( $parent = $class->getParent() ) !== null ) {
            if( $parent->getName() === $parentName )

                return true;

            $class = $parent;
        }

        return false;
    }

    /**
     * Returns class interfaces
     *
     * @return \Iterator
     */
    public function getInterfacesIterator()
    {
        $iterator = new \ArrayIterator( $this->interfaces );

        if ($this->parent) {
            $mIterator = new \MultipleIterator( \MultipleIterator::MIT_NEED_ALL | \MultipleIterator::MIT_KEYS_ASSOC );
            $mIterator->attachIterator( $iterator );
            $mIterator->attachIterator( $this->parent->getInterfacesIterator() );

            $iterator = $mIterator;
        }

        return $iterator;
    }

    /**
     * Checks, whether class implements given parent
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface( $interfaceName )
    {
        return isset( $this->interfaces[ $interfaceName ] ) ||
            ( $this->parent && $this->parent->implementsInterface( $interfaceName ) );
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
