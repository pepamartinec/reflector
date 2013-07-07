<?php
namespace Reflector;

use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\Code\StaticNamespaceReflection;
use Reflector\Reflection\Dummy\DummyClassReflection;
use Reflector\Reflection\Dummy\DummyInterfaceReflection;
use Reflector\Reflection\Dummy\DummyReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;
use Reflector\Reflection\Runtime\RuntimeClassReflection;
use Reflector\Reflection\Runtime\RuntimeInterfaceReflection;

class ReflectionRegistry {

    const GLOBAL_NAMESPACE = '';

    /**
     * @var NamespaceReflectionInterface[]
     */
    private $namespaces = array();

    /**
     * Checks whether the namespace has been already analyzed.
     *
     * @param  string $name
     * @return bool
     */
    public function hasNamespace($name)
    {
        return isset($this->namespaces[$name]);
    }

    /**
     * Returns namespace reflection
     *
     * @param  string $name fully classified namespace name
     * @return NamespaceReflectionInterface
     */
    public function getNamespace($name)
    {
        if (!$this->hasNamespace($name)) {
            $this->namespaces[$name] = new StaticNamespaceReflection($name, $this);
        }

        return $this->namespaces[$name];
    }

    /**
     * Returns global namespace reflection.
     *
     * @return NamespaceReflectionInterface
     */
    public function getGlobalNamespace()
    {
        return $this->getNamespace(self::GLOBAL_NAMESPACE);
    }

    /**
     * Checks if the interface is known.
     *
     * @param string $name
     * @return bool
     */
    public function hasInterface($name)
    {
        list($nsName, $itName) = self::explodeItemName($name);

        if (!$this->hasNamespace($nsName)) {
            return false;
        }

        $namespace = $this->getNamespace($nsName);

        if (!$namespace->hasItem($itName)) {
            return false;
        }

        $item = $namespace->getItem($itName);

        return $item instanceof InterfaceReflectionInterface
           && !$item instanceof DummyReflectionInterface;
    }

    /**
     * Returns reflection for given interface
     *
     * @param string      $name fully qualified interface name
     * @param string|null $file
     * @param int|null    $line
     *
     * @throws InvalidItemException
     * @return InterfaceReflectionInterface
     *
     */
    public function getInterface($name, $file = null, $line = null)
    {
        list($nsName, $itName) = self::explodeItemName($name);

        $namespace = $this->getNamespace($nsName);

        if (!$namespace->hasItem($itName)) {
            if (interface_exists($name, false)) {
                $reflection = new RuntimeInterfaceReflection(new \ReflectionClass($name), $this);
            } else {
                $reflection = new DummyInterfaceReflection($namespace, $itName, $file, $line);
            }

            $namespace->addItem($reflection);
        }

        $item = $namespace->getItem($itName);

        if (!$item instanceof InterfaceReflectionInterface) {
            $hint = get_class($item);

            $error = "Item '{$name}' is expected to be an InterfaceReflection item";

            if ($file) {
                $error .= ' at '.$file;

                if ($line) {
                    $error .= ':'.$line;
                }
            }

            $error .= ", {$hint} found instead (defined at {$item->getFileName()}:{$item->getStartLine()})";

            throw new InvalidItemException($error);
        }

        return $item;
    }


    /**
     * Checks if the class is known.
     *
     * @param string $name
     * @return bool
     */
    public function hasClass($name)
    {
        list($nsName, $itName) = self::explodeItemName($name);

        if (!$this->hasNamespace($nsName)) {
            return false;
        }

        $namespace = $this->getNamespace($nsName);

        if (!$namespace->hasItem($itName)) {
            return false;
        }

        $item = $namespace->getItem($itName);

        return $item instanceof ClassReflectionInterface
           && !$item instanceof DummyReflectionInterface;
    }

    /**
     * Returns the class reflection.
     *
     * @param string      $name fully qualified class name
     * @param string|null $file
     * @param int|null    $line
     *
     * @throws InvalidItemException
     * @return ClassReflectionInterface
     *
     */
    public function getClass($name, $file = null, $line = null)
    {
        list($nsName, $clName) = self::explodeItemName($name);

        $namespace = $this->getNamespace($nsName);

        if (!$namespace->hasItem($clName)) {
            if (class_exists($name, true)) {
                $reflection = new RuntimeClassReflection(new \ReflectionClass($name), $this);
            } else {
                $reflection = new DummyClassReflection($namespace, $clName, $file, $line);
            }

            $namespace->addItem($reflection);
        }

        $item = $namespace->getItem($clName);

        if (!$item instanceof ClassReflectionInterface) {
            throw new InvalidItemException("Requested item '{$name}' is not a class reflection");
        }

        return $item;
    }

    /**
     * Explodes FQN of namespace item to namespace and local name parts.
     *
     * @param string $name
     * @return array
     */
    public static function explodeItemName($name)
    {
        if ($name[0] === '\\') {
            $name = substr($name, 1);
        }

        $slashPos = strrpos($name, '\\');

        if ($slashPos === false) {
            return array(self::GLOBAL_NAMESPACE, $name);

        } else {
            return array(substr($name, 0, $slashPos), substr($name, $slashPos + 1));
        }
    }
}
