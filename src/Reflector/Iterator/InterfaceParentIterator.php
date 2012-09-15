<?php
namespace Reflector\Iterator;

use Reflector\Reflection\InterfaceReflectionInterface;

class InterfaceParentIterator extends \ArrayIterator implements \RecursiveIterator
{
    const INCLUDE_SELF = 1;

    /**
     * Constructor
     *
     * @param InterfaceReflectionInterface $interface
     * @param int                          $options
     */
    public function __construct(InterfaceReflectionInterface $interface, $options = 0)
    {
        $parents = $interface->getParents();

        if ($options & self::INCLUDE_SELF) {
            array_unshift($parents, $interface);
        }

        parent::__construct($parents);
    }

    /**
     * @see \RecursiveIterator::getChildren()
     * @return InterfaceParentIterator
     */
    public function getChildren()
    {
        return new self($this->current()->getParents());
    }

    /**
     * @see \RecursiveIterator::hasChildren()
     */
    public function hasChildren()
    {
        return sizeof($this->current()->getParents()) > 0;
    }
}
