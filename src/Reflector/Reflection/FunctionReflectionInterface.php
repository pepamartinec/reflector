<?php
namespace Reflector\Reflection;

interface FunctionReflectionInterface extends ReflectionInterface
{
    /**
     * Returns definition file name
     *
     * @return string|null
     */
    public function getFileName();

    /**
     * Returns line number within definition file
     *
     * @return int|null
     */
    public function getStartLine();

    /**
     * Returns containing namespace
     *
     * @return iReflectionNamespace
     */
    public function getNamespace();

    /**
     * Returns name
     *
     * @return string
     */
    public function getName();
}
