<?php
namespace Reflector\Reflection;

interface InterfaceReflectionInterface extends ReflectionInterface
{
    /**
     * Returns viewConfiguration file name
     *
     * @return string|null
     */
    public function getFileName();

    /**
     * Returns line number within viewConfiguration file
     *
     * @return int|null
     */
    public function getStartLine();

    /**
     * Returns the parent namespace
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
     * Returns the fully qualified interface name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns list of the interface parents
     *
     * @return array
     */
    public function getParents();

    /**
     * Returns the parents list iterator
     *
     * @return \Iterator
     */
    public function getParentIterator();

    /**
     * Checks, wheter interface has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent($parentName);
}
