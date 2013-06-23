<?php
namespace Reflector\Reflection;

/**
 * Class reflection interface
 *
 * Describes the interface shared by the class reflections
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
interface ClassReflectionInterface extends ReflectionInterface, NamespaceItemInterface
{
    /**
     * Returns the direct parent class
     *
     * @return ClassReflectionInterface|null
     */
    public function getParent();

    /**
     * Returns the class parent iterator
     *
     * @return \Iterator
     */
    public function getParentIterator();

    /**
     * Checks if the class has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent($parentName);

    /**
     * Returns the list of interfaces directly implemented by the class
     *
     * @return array
     */
    public function getInterfaces();

    /**
     * Returns class interfaces iterator
     *
     * @return \Iterator
     */
    public function getInterfaceIterator();

    /**
     * Checks if the class implements given interface
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface($interfaceName);

    /**
     * Checks if the class is abstract
     *
     * @return bool
     */
    public function isAbstract();

    /**
     * Checks if the class is final
     *
     * @return bool
     */
    public function isFinal();
}
