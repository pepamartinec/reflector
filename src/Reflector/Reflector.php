<?php
namespace Reflector;

class Reflector
{
    /**
     * @var ReflectionFactory
     */
    protected $factory;

    /**
     * Constructs new ReflectionFactory
     */
    public function __construct()
    {
        $this->factory = new ReflectionFactory();
    }

    /**
     * Analyzes whole directory
     *
     * @param string $dirName
     * @param bool   $recursive
     */
    public function analyzeDirectory($dirName, $recursive = true)
    {
        if (!is_dir($dirName)) {
            throw new InvalidFileException($dirName);
        }

        $iterator = null;

        if ($recursive) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dirName));
        } else {
            $iterator = new \DirectoryIterator($dirName);
        }

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            if (substr($item->getFilename(), -4) !== '.php') {
                continue;
            }

            $this->analyzeFile($item->getPathname());
        }
    }

    /**
     * Analyzes whole file and returns found namespaces
     *
     * @param string $fileName
     *
     * @throws InvalidFileException
     * @throws InvalidSyntaxException
     */
    public function analyzeFile($filename)
    {
        $this->factory->analyzeFile($filename);
    }

    /**
     * Return namespaces iterator
     *
     * @return \Iterator
     */
    public function getNamespacesIterator()
    {
        return new \ArrayIterator($this->namespaces);
    }

    /**
     * Returns namespace reflection
     *
     * @param string $name fully classified namespace name
     * @return NamespaceReflectionInterface|null
     */
    public function getNamespace($name)
    {
        if ($this->factory->hasNamespace($name)) {
            return $this->factory->getNamespace($name);

        } else {
            return null;
        }
    }

    /**
     * Returns reflection for given interface
     *
     * @param string $name fully qualified interface name
     * @return InterfaceReflectionInterface|null
     */
    public function getInterface($name)
    {
        if ($this->factory->hasInterface($name)) {
            return $this->factory->getInterface($name);

        } else {
            return null;
        }
    }

    /**
     * Returns reflection for given class
     *
     * @param string $name fully qualified class name
     * @return ClassReflectionInterface|null
     */
    public function getClass($name)
    {
        if ($this->factory->hasClass($name)) {
            return $this->factory->getClass($name);

        } else {
            return null;
        }
    }
}

