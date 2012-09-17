<?php
namespace Reflector\Reflection\Runtime;
use Reflector\Iterator\InterfaceParentIterator;

use Reflector\Tokenizer\Tokenizer;
use Reflector\ReflectionFactory;
use Reflector\Reflection\RuntimeReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;

class RuntimeInterfaceReflection implements InterfaceReflectionInterface, RuntimeReflectionInterface
{
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     * @var NamespaceReflectionInterface
     */
    protected $namespace;

    /**
     * @var array
     */
    protected $parents;

    /**
     * Constructs new reflection
     *
     * @param \ReflectionClass  $reflection
     * @param ReflectionFactory $f
     */
    public function __construct(\ReflectionClass $reflection, ReflectionFactory $f)
    {
        $this->reflection = $reflection;

        list($nsName, $itName) = Tokenizer::explodeName('\\' . $this->reflection->getName());
        $this->namespace = $f->getNamespace($nsName);

        $this->parents = array();
        foreach ($reflection->getInterfaces() as $parent) {
            $this->parents[] = new RuntimeInterfaceReflection($parent, $f);
        }
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
        return $this->reflection->export($argument, $return);
    }

    /**
     * @see Reflector::__toString()
     */
    public function __toString()
    {
        return $this->reflection->__toString();
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getFileName()
     */
    public function getFileName()
    {
        return $this->reflection->getFileName();
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getStartLine()
     */
    public function getStartLine()
    {
        return $this->reflection->getStartLine();
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
        return $this->reflection->getShortName();
    }

    /**
     * @see Reflector\Reflection.InterfaceReflectionInterface::getName()
     */
    public function getName()
    {
        return $this->namespace->getName() . '\\' . $this->getName();
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
        $iface = $this;
        while (($parent = $iface->getParent()) !== null) {
            if ($parent->getName() === $parentName) {
                return true;
            }

            $iface = $parent;
        }

        return false;
    }

    /**
     * Returns interfaces (this and every parent)
     *
     * @return array
     */
    public function getInterfaces()
    {
        return $this->interfaces;
    }
}
