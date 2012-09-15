<?php
namespace Reflector;

use Reflector\Reflection\Dummy\DummyClassReflection;
use Reflector\Reflection\Runtime\RuntimeClassReflection;
use Reflector\Reflection\Dummy\DummyInterfaceReflection;
use Reflector\Reflection\Runtime\RuntimeInterfaceReflection;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\DummyReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Tokenizer\UnexpectedTokenException;
use Reflector\Reflection\Code\StaticNamespaceReflection;
use Reflector\Tokenizer\Tokenizer;

class ReflectionFactory
{
    /**
     * @var array
     */
    protected $namespaces;

    /**
     * @var array
     */
    protected $analyzedFiles;

    /**
     * Constructs new ReflectionFactory
     */
    public function __construct()
    {
        $this->namespaces    = array();
        $this->analyzedFiles = array();
    }

    /**
     * Analyzes the file
     *
     * @param string $filename
     *
     * @throws InvalidFileException
     * @throws InvalidSyntaxException
     */
    public function analyzeFile($filename)
    {
        if (!is_file($filename)) {
            throw new InvalidFileException($filename);
        }

        if (isset($this->analyzedFiles[$filename])) {
            return;
        }

        $aliasResolver = new AliasResolver($filename);
        $tokenizer     = Tokenizer::fromFile($filename);

        $this->analyzedFiles[$filename] = $this->analyzeCode($tokenizer, $aliasResolver);
    }

    /**
     * Analyzes given source code and returns found namespaces
     *
     * @param  Tokenizer     $t
     * @param  AliasResolver $r
     *
     * @throws InvalidSyntaxException
     */
    public function analyzeCode(Tokenizer $t, AliasResolver $r)
    {
        $localNs = array();

        $withNamespace = $t->checkToken(T_OPEN_TAG, null, 0)
                    && (($t->checkToken(T_NAMESPACE, null, 1)) || ($t->checkToken(T_WHITESPACE, null, 1)
                    && $t->checkToken(T_NAMESPACE, null, 2)));

        try {
            // namespace specified
            // multiple namespaces may occur
            if ($withNamespace) {
                // T_OPEN_TAG
                $t->expectToken(T_OPEN_TAG);
                $token = $t->nextToken();

                do {
                    list($name, $isBracketed) = StaticNamespaceReflection::parseHead($t);

                    $namespace = $this->getNamespace($name);
                    $namespace->parseBody($isBracketed, $t, $r);

                    $localNs[$name] = $namespace;
                } while ($t->getToken());


            // no namespace specified
            // whole code has to be in global namespace
            } else {
                // HTML before code
                if ($t->checkToken(T_INLINE_HTML)) {
                    $token = $t->nextToken();
                }

                // T_OPEN_TAG
                $t->expectToken(T_OPEN_TAG);
                $token = $t->nextToken();

                $namespace = $this->getNamespace('\\');
                $namespace->parseBody(false, $t, $r);

                $localNs[$name] = $namespace;
            }

        } catch (UnexpectedTokenException $e) {
            throw new InvalidSyntaxException($e->getMessage(), null, $e);
        }
    }

    /**
     * Checks whether the namespace has been already analyzed.
     *
     * @param string $name
     * @return bool
     */
    public function hasNamespace($name)
    {
        return isset($this->namespaces[$name]);
    }

    /**
     * Checks whether the interface has been already analyzed
     *
     * @param string $name
     * @return bool
     */
    public function hasInterface($name)
    {
    	list($nsName, $itName) = Tokenizer::explodeName($name);

    	if (!$this->hasNamespace($nsName)) {
    		return false;
    	}

    	$namespace = $this->getNamespace($nsName);

    	if(!$namespace->hasItem($itName)) {
    		return false;
    	}

    	$item = $namespace->getItem($itName);

    	return $item instanceof InterfaceReflectionInterface
    	   && !$item instanceof DummyReflectionInterface;
    }

    /**
     * Checks whether the class has been already analyzed
     *
     * @param string $name
     * @return bool
     */
    public function hasClass($name)
    {
    	list($nsName, $itName) = Tokenizer::explodeName($name);

    	if (!$this->hasNamespace($nsName)) {
    		return false;
    	}

    	$namespace = $this->getNamespace($nsName);

    	if(!$namespace->hasItem($itName)) {
    		return false;
    	}

    	$item = $namespace->getItem($itName);

    	return $item instanceof ClassReflectionInterface
    	   && !$item instanceof DummyReflectionInterface;
    }

    /**
     * Returns namespace reflection
     *
     * @param string $name fully classified namespace name
     * @return NamespaceReflectionInterface
     */
    public function getNamespace($name)
    {
        // namespace not defined yet, create new empty one
        if (!$this->hasNamespace($name)) {
            $this->namespaces[$name] = new StaticNamespaceReflection($name, $this);
        }

        return $this->namespaces[$name];
    }

    /**
     * Returns reflection for given interface
     *
     * @param  string $name fully qualified interface name
     * @return InterfaceReflectionInterface
     *
     * @throws InvalidItemException
     */
    public function getInterface($name)
    {
        list($nsName, $itName) = Tokenizer::explodeName($name);

        $namespace = $this->getNamespace($nsName);

        if (!$namespace->hasItem($itName)) {
            if (interface_exists($name, false)) {
                $reflection = new RuntimeInterfaceReflection(new \ReflectionClass(strtolower($name)), $this);
            } else {
                $reflection = new DummyInterfaceReflection($namespace, $itName);
            }

            $namespace->addItem($reflection);
        }

        $item = $namespace->getItem($itName);

        if ($item instanceof InterfaceReflectionInterface === false) {
            throw new InvalidItemException('Requested item is not an interface reflection');
        }

        return $item;
    }

    /**
     * Returns reflection for given class
     *
     * @param  string $name fully qualified class name
     * @return ClassReflectionInterface
     *
     * @throws InvalidItemException
     */
    public function getClass($name)
    {
        list($nsName, $clName) = Tokenizer::explodeName($name);

        $namespace = $this->getNamespace($nsName);

        if ($namespace->hasItem($clName) === false) {
            if (class_exists($name, true)) {
                $reflection = new RuntimeClassReflection(new \ReflectionClass(strtolower($name)), $this);
            } else {
                $reflection = new DummyClassReflection($namespace, $clName);
            }

            $namespace->addItem($reflection);
        }

        $item = $namespace->getItem($clName);

        if ($item instanceof ClassReflectionInterface === false) {
            throw new InvalidItemException('Requested item is not a class reflection');
        }

        return $item;
    }
}
