<?php
namespace Reflector\Reflection;

interface ClassReflectionInterface extends ReflectionInterface
{
    /**
     * Returns the definition file name
     *
     * @return string|null
     */
    public function getDefinitionFile();

    /**
     * Returns the line number within the definition file
     *
     * @return int|null
     */
    public function getStartLine();

    /**
     * Returns the containing namespace
     *
     * @return iReflectionNamespace
     */
    public function getNamespace();

    /**
     * Returns the class name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the fully qualified class name
     *
     * @return string
     */
    public function getFullName();

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
     * Checks, wheter the class has given parent
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
     * Checks, whether class implements given parent
     *
     * @param  string $interfaceName
     * @return bool
     */
    public function implementsInterface($interfaceName);

    /**
     * Tells whether class is abstract
     *
     * @return bool
     */
    public function isAbstract();

    /**
     * Tells whether class is final
     *
     * @return bool
     */
    public function isFinal();
}

