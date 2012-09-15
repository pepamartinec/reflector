<?php
namespace Reflector\Reflection\Code;

use Reflector\Iterator\CallbackFilterIterator;
use Reflector\Reflection\RuntimeReflectionInterface;
use Reflector\Reflection\DummyReflectionInterface;
use Reflector\Reflection\FunctionReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\RedeclarationException;
use Reflector\InvalidItemException;
use Reflector\Reflection\ReflectionInterface;
use Reflector\AliasResolver;
use Reflector\Tokenizer\Tokenizer;
use Reflector\Tokenizer\UnexpectedTokenException;
use Reflector\ReflectionFactory;
use Reflector\Reflection\StaticReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;

class StaticNamespaceReflection implements NamespaceReflectionInterface, StaticReflectionInterface
{
    /**
     * @var ReflectionFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var NamespaceReflectionInterface|null
     */
    protected $parent;

    /**
     * @var array
     */
    protected $items;

    /**
     * Constructs new namespace reflection
     *
     * @param string            $fullName
     * @param ReflectionFactory $f
     *
     * @throws UnexpectedTokenException
     */
    public function __construct( $name, ReflectionFactory $f )
    {
        $this->factory = $f;

        if ($name === '\\') {
            $this->name   = $name;
            $this->parent = null;

        } else {
            list( $parentName, $localName ) = Tokenizer::explodeName( $name );

            $this->parent = $f->getNamespace( $parentName );

            if ($parentName !== '\\') {
                $parentName .= '\\';
            }

            $this->name = $parentName . $localName;
        }

        $this->items  = array();
    }

    /**
     * Parses another chunk of namespace
     *
     * @param  Tokenizer $t
     * @return array     array( $name, $isBracketed )
     *
     * @throws UnexpectedTokenException
     */
    public static function parseHead( Tokenizer $t  )
    {
        // T_NAMESPACE
        $t->expectToken( T_NAMESPACE );
        $token = $t->nextToken();

        // global namespace
        if ( $t->checkToken( '{' ) ) {
            $name = '\\';
            $isBracketed = true;

            $token = $t->nextToken();

        // named namespace
        } else {
            $token = $t->parseName( $name );

            // prepend absolute path indicator
            // (namespace names are always absolute)
            // TODO is this right?
            if ($name[0] !== '\\') {
                $name = '\\'.$name;
            }

            // bracketed namespace
            if ( $t->checkToken( '{' ) ) {
                $isBracketed = true;

            // simple namespace
            } elseif ( $t->checkToken( ';' ) ) {
                $isBracketed = false;

            } else {
                throw new UnexpectedTokenException( $token );
            }

            $token = $t->nextToken();
        }

        return array( $name, $isBracketed );
    }

    /**
     * Parses another chunk of namespace
     *
     * @param bool          $isBracketed
     * @param Tokenizer     $t
     * @param AliasResolver $r
     *
     * @throws UnexpectedTokenException
     * @throws UnexpectedEndOfSourceException
     */
    public function parseBody( $isBracketed, Tokenizer $t, AliasResolver $r  )
    {
        $token = $t->getToken();

        // parse content
        do {
            if ( is_array( $token ) === false ) {
                // possible end of namespace
                if ($token === '}') {
                    if ($isBracketed) {
                        return $t->nextToken();
                    } else {
                        throw new UnexpectedTokenException( $token );
                    }

                // nested brackets block
                } elseif ($token === '{') {
                    $token = $t->parseBracketsBlock( $t );

                // who knows??
                } else {
                    $token = $t->nextToken();
                }

                continue;
            }

            switch ($token[0]) {
                // end of namespace (another namespace follows)
                case T_NAMESPACE:
                    if ($isBracketed) {
                        throw new UnexpectedTokenException( $token );
                    } else {
                        return $token;
                    }
                    break;

                case T_USE:
                    $token = $r->parseAliases( $this->name, $t );
                    break;

                // interface
                case T_INTERFACE:
                    $interface = new StaticInterfaceReflection( $this, $t, $r );
                    $token = $t->getToken();

                    $this->addItem( $interface );
                    break;

                // class
                case T_FINAL:
                case T_ABSTRACT:
                case T_CLASS:
                    $class = new StaticClassReflection( $this, $t, $r );
                    $token = $t->getToken();

                    $this->addItem( $class );
                    break;

                default:
                    throw new UnexpectedTokenException( $token );
            }

        // while not end of token stream
        } while ( $token !== null );

        if ($isBracketed) {
            throw new UnexpectedTokenException( null, '}' );
        }

        return null;
    }

    /**
     * Exports a namespace
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
     * Returns the string representation of the ReflectionNamespace object
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns namespace name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns direct parent namespace
     *
     * @return iReflectionClass
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Checks, wheter namespace has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName )
    {
        $class = $this;
        while ( ( $parent = $class->getParent() ) !== null ) {
            if ( $parent->getName() === $parentName ) {
                return true;
            }

            $class = $parent;
        }

        return false;
    }

    /**
     * Adds new item (class, interface, funciton) reflection into namespace
     *
     * @param ReflectionInterface $item
     *
     * @throws InvalidItemException
     * @throws RedeclarationException
     */
    public function addItem( ReflectionInterface $item )
    {
        if (
            $item instanceof ClassReflectionInterface === false &&
            $item instanceof InterfaceReflectionInterface === false &&
            $item instanceof FunctionReflectionInterface === false
) {
            throw new InvalidItemException( 'Invalid reflection type '.get_class($item).', expected ClassReflectionInterface, InterfaceReflectionInterface or FunctionReflectionInterface' );
        }

        if ( $item->getNamespace() !== $this ) {
            throw new InvalidItemException( 'Item comes from other namespace' );
        }

        if ( isset( $this->items[ $item->getName() ] ) ) {
            $previous = $this->items[ $item->getName() ];

            $isReplacable = $previous instanceof DummyReflectionInterface && ( $item instanceof RuntimeReflectionInterface || $item instanceof StaticReflectionInterface ) ||
                            $previous instanceof RuntimeReflectionInterface && $item instanceof StaticReflectionInterface;

            if ($isReplacable === false) {
                throw new RedeclarationException( "Namespace {$this->getName()} already contains {$item->getName()}, previously declarladerd at {$previous->getDefinitionFile()}:{$previous->getStartLine()}" );
            }
        }

        $this->items[ $item->getName() ] = $item;
    }

    /**
     * Checks, wheter namespace contains given item (class, interface, function)
     *
     * @param  string $itemName
     * @return bool
     */
    public function hasItem( $itemName )
    {
        return isset( $this->items[ $itemName ] );
    }

    /**
     * Returns given item (class, interface, function)
     *
     * @param  string                   $itemName
     * @return ReflectionInterface|null
     */
    public function getItem( $itemName )
    {
        if ( ! isset( $this->items[ $itemName ] ) ) {
            return null;
        }

        return $this->items[ $itemName ];
    }

    /**
     * Returns namespace classes iterator
     *
     * @return \Iterator
     */
    public function getClassIterator()
    {
        $iterator = new \ArrayIterator( $this->items );
        $filter   = function( $current ) { return $current instanceof ClassReflectionInterface; };

        return new CallbackFilterIterator( $iterator, $filter );
    }

    /**
     * Returns namespace interfaces iterator
     *
     * @return \Iterator
     */
    public function getInterfaceIterator()
    {
        $iterator = new \ArrayIterator( $this->items );
        $filter   = function( $current ) { return $current instanceof InterfaceReflectionInterface; };

        return new CallbackFilterIterator( $iterator, $filter );
    }

    /**
     * Returns namespace global functions iterator
     *
     * @return \Iterator
     */
    public function getFunctionIterator()
    {
        $iterator = new \ArrayIterator( $this->items );
        $filter   = function( $current ) { return $current instanceof FunctionReflectionInterface; };

        return new CallbackFilterIterator( $iterator, $filter );
    }
}
