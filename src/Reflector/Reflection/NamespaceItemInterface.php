<?php
namespace Reflector\Reflection;

/**
 * Namespace item interface
 *
 * Describes the namespace item, such a class or function.
 *
 * @author Josef Martinec <joker806@gmail.com>
 */
interface NamespaceItemInterface
{
    /**
     * Returns the containing namespace
     *
     * @return NamespaceReflectionInterface
     */
    public function getNamespace();

    /**
     * Returns the class name
     *
     * @return string
     */
    public function getShortName();

    /**
     * Returns the fully qualified class name
     *
     * @return string
     */
    public function getName();

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
}
