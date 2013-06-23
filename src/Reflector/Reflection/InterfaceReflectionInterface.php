<?php
namespace Reflector\Reflection;

/**
 * Interface reflection interface
 *
 * Describes the interface shared by the interface reflections
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
interface InterfaceReflectionInterface extends ReflectionInterface, NamespaceItemInterface
{
    /**
     * Returns the definition file name
     *
     * @return string|null
     */
    public function getFileName();

    /**
     * Returns the line number within the definition file
     *
     * @return int|null
     */
    public function getStartLine();

    /**
     * Returns the direct parent interfaces
     *
     * @return array
     */
    public function getParents();

    /**
     * Returns the interface parent iterator
     *
     * @return \Iterator
     */
    public function getParentIterator();

    /**
     * Checks if the interface has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent($parentName);
}
