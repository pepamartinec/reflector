<?php
namespace Reflector\Reflection;

interface NamespaceReflectionInterface extends ReflectionInterface
{
    /**
     * Returns namespace name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns direct parent namespace
     *
     * @return iReflectionClass
     */
    public function getParent();

    /**
     * Checks, wheter namespace has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent( $parentName );

    /**
     * Adds new item (class, interface, funciton) reflection into namespace
     *
     * @param iReflection $item
     *
     * @throws InvalidItemException
     */
    public function addItem( ReflectionInterface $item );

    /**
     * Checks, wheter namespace contains given item (class, interface, function)
     *
     * @param  string $itemName
     * @return bool
     */
    public function hasItem( $itemName );

    /**
     * Returns given item (class, interface, function)
     *
     * @param  string                   $itemName
     * @return ReflectionInterface|null
     */
    public function getItem( $itemName );

    /**
     * Returns namespace classes iterator
     *
     * @return \Iterator
     */
    public function getClassIterator();

    /**
     * Returns namespace interfaces iterator
     *
     * @return \Iterator
     */
    public function getInterfaceIterator();

    /**
     * Returns namespace global functions iterator
     *
     * @return \Iterator
     */
    public function getFunctionIterator();
}
