<?php
namespace Reflector\Reflection\Code;

use Reflector\AliasResolver;
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
use Reflector\ReflectionFactory;
use Reflector\Tokenizer\Tokenizer;
use Reflector\Tokenizer\UnexpectedTokenException;

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
     * @var NamespaceItemInterface[]
     */
    protected $items;

    /**
     * Constructs new namespace reflection
     *
     * @param string            $name
     * @param ReflectionFactory $f
     *
     * @throws UnexpectedTokenException
     */
    public function __construct($name, ReflectionFactory $f)
    {
        $this->factory = $f;

        if ($name === '') {
            $this->name   = $name;
            $this->parent = null;

        } else {
            list($parentName, $localName) = Tokenizer::explodeName($name);

            $this->name   = $localName;
            $this->parent = $f->getNamespace($parentName);
        }

        $this->items = array();
    }

    /**
     * Parses another chunk of namespace
     *
     * @param  Tokenizer $t
     * @return array     array( $name, $isBracketed )
     *
     * @throws UnexpectedTokenException
     */
    public static function parseHead(Tokenizer $t)
    {
        // T_NAMESPACE
        $t->expectToken(T_NAMESPACE);
        $token = $t->nextToken();

        // global namespace
        if ($t->checkToken('{')) {
            $name        = '';
            $isBracketed = true;

            $token = $t->nextToken();

            // named namespace
        } else {
            $token = $t->parseName($name);

            // bracketed namespace
            if ($t->checkToken('{')) {
                $isBracketed = true;

                // simple namespace
            } elseif ($t->checkToken(';')) {
                $isBracketed = false;

            } else {
                throw new UnexpectedTokenException($token);
            }

            $token = $t->nextToken();
        }

        return array($name, $isBracketed);
    }

    /**
     * Parses another chunk of namespace
     *
     * @param bool          $isBracketed
     * @param Tokenizer     $t
     * @param AliasResolver $r
     *
     * @return array|null
     * @throws UnexpectedTokenException
     */
    public function parseBody($isBracketed, Tokenizer $t, AliasResolver $r)
    {
        $token = $t->getToken();

        // parse content
        do {
            if (is_array($token) === false) {
                // possible end of namespace
                if ($token === '}') {
                    if ($isBracketed) {
                        return $t->nextToken();
                    } else {
                        throw new UnexpectedTokenException($token);
                    }

                    // nested brackets block
                } elseif ($token === '{') {
                    $token = $t->parseBracketsBlock($t);

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
                        throw new UnexpectedTokenException($token);
                    } else {
                        return $token;
                    }
                    break;

                case T_USE:
                    $token = $r->parseAliases($this->name, $t);
                    break;

                // interface
                case T_INTERFACE:
                    $interface = new StaticInterfaceReflection($this, $t, $r);
                    $token     = $t->getToken();

                    $this->addItem($interface);
                    break;

                // class
                case T_FINAL:
                case T_ABSTRACT:
                case T_CLASS:
                    $class = new StaticClassReflection($this, $t, $r);
                    $token = $t->getToken();

                    $this->addItem($class);
                    break;

                default:
                    throw new UnexpectedTokenException($token);
            }

            // while not end of token stream
        } while ($token !== null);

        if ($isBracketed) {
            throw new UnexpectedTokenException(null, '}');
        }

        return null;
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

            if ($isReplaceable) {
                throw new RedeclarationException("Namespace {$this->getShortName()} already contains {$item->getName(
                )}, previously declared at {$previous->getFileName()}:{$previous->getStartLine()}");
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
