<?php
namespace Reflector\Reflection;

/**
 * Interface reflection interface
 *
 * Describes the interface shared by the interface reflections
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
interface InterfaceReflectionInterface extends ReflectionInterface
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
     * Returns the containing namespace
     *
     * @return NamespaceReflectionInterface
     */
    public function getNamespace();

    /**
     * Returns the interface name
     *
     * @return string
     */
    public function getShortName();

    /**
     * Returns the fully qualified inteface name
     *
     * @return string
     */
    public function getName();

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
