<?php
namespace Reflector\Iterator;

use Reflector\Reflection\ClassReflectionInterface;

/**
 * The class interface iterator
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
class ClassInterfaceIterator extends \AppendIterator
{
    /**
     * @var ClassParentIterator
     */
    protected $parentIterator;

    /**
     * @var array
     */
    private $visited;

    /**
     * Class constructor
     *
     * @param ClassReflectionInterface $class
     */
    public function __construct(ClassReflectionInterface $class)
    {
        parent::__construct();

        $this->parentIterator = new ClassParentIterator($class, ClassParentIterator::INCLUDE_SELF);

        $this->rewind();
    }

    /**
     * @see \AppendIterator::next()
     * @return ClassReflectionInterface
     */
    public function next()
    {
        do {
            parent::next();

            $this->adjustCursor();

        } while ($this->valid() && $this->isVisited());

        $this->markVisited();
    }

    /**
     * @see AppendIterator::rewind()
     */
    public function rewind()
    {
        parent::rewind();

        $this->visited = array();
        $this->adjustCursor();
        $this->markVisited();
    }

    /**
     * Adjusts the iterator cursor to a valid value (if exists)
     */
    private function adjustCursor()
    {
        $pi = $this->parentIterator;

        while (!$this->valid() && $pi->valid()) {
            foreach ($pi->current()->getInterfaces() as $interface) { /* @var $interface */
                $it = new InterfaceParentIterator($interface, InterfaceParentIterator::INCLUDE_SELF);
                $this->append($it);
            }

            $pi->next();
        }
    }

    /**
     * Tells if the current item has already been visited
     *
     * @return bool
     */
    private function isVisited()
    {
        $name = $this->current()->getFullName();
        return isset($this->visited[$name]);
    }

    /**
     * Marks the current item as visited
     */
    private function markVisited()
    {
        if (!$this->valid()) {
            return;
        }

        $name = $this->current()->getFullName();
        $this->visited[$name] = true;
    }
}
