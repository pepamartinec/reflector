<?php
namespace Reflector\Reflection;

/**
 * Namespace reflection interface
 *
 * Describes the interface shared by the namespace reflections
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
interface NamespaceReflectionInterface extends ReflectionInterface
{
    /**
     * Returns the namespace name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns direct parent namespace
     *
     * @return NamespaceReflectionInterface|null
     */
    public function getParent();

    /**
     * Checks if the namespace has given parent
     *
     * @param  string $parentName
     * @return bool
     */
    public function hasParent($parentName);

    /**
     * Adds new item into the namespace
     *
     * @param NamespaceItem $item
     *
     * @throws InvalidItemException when item cannot be added to the namespace
     */
    public function addItem(NamespaceItem $item);

    /**
     * Checks if the namespace contains the item
     *
     * @param  string $itemName
     * @return bool
     */
    public function hasItem($itemName);

    /**
     * Returns the item
     *
     * @param  string $itemName
     * @return NamespaceItem|null
     */
    public function getItem($itemName);

    /**
     * Returns the namespace class iterator
     *
     * @return \Iterator
     */
    public function getClassIterator();

    /**
     * Returns the namespace interface iterator
     *
     * @return \Iterator
     */
    public function getInterfaceIterator();
}
