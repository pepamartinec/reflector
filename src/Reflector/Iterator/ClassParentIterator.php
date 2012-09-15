<?php
namespace Reflector\Iterator;

use Reflector\Reflection\ClassReflectionInterface;

class ClassParentIterator implements \Iterator
{
    const INCLUDE_SELF = 1;

    /**
     * @var int
     */
    protected $options;

	/**
	 * @var ClassReflectionInterface
	 */
	protected $class;

	/**
	 * @var ClassReflectionInterface
	 */
	protected $current;

   /**
     * Class constructor
     *
     * @param ClassReflectionInterface $class
     * @param int $options
     */
    public function __construct(ClassReflectionInterface $class, $options = 0)
    {
    	$this->class   = $class;
    	$this->options = $options;

    	$this->rewind();
    }

    /**
     * @see \Iterator::current()
     * @return ClassReflectionInterface|null
     */
    public function current()
    {
    	return $this->current;
    }

    /**
     * @see \Iterator::next()
     */
    public function next()
    {
    	if ($this->current) {
    		$this->current = $this->current->getParent();
    	}
    }

    /**
     * @see \Iterator::key()
     */
    public function key()
    {
    	return $this->current->getFullName();
    }

    /**
     * @see \Iterator::valid()
     */
    public function valid()
    {
    	return $this->current !== null;
    }

    /**
     * @see \Iterator::rewind()
     */
    public function rewind()
    {
        if ($this->options & self::INCLUDE_SELF) {
            $this->current = $this->class;

        } else {
            $this->current = $this->class->getParent();
        }
    }
}
