<?php
namespace Reflector;

use Reflector\Tokenizer\Tokenizer;
use Reflector\Reflection\NamespaceReflectionInterface;

class AliasResolver
{
    /**
     * @var ReflectionFactory
     */
    protected $factory;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var array
     */
    protected $aliases;

    /**
     * Constructs new NameResolver
     *
     * @param string $fileName
     */
    public function __construct(ReflectionFactory $factory, $fileName = null)
    {
        $this->factory  = $factory;
        $this->fileName = $fileName;
        $this->aliases  = array();
    }

    /**
     * Returns resolver fileName
     *
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName();
    }

    /**
     * Parses aliases definition block
     *
     * @param  Tokenizer $t
     * @return array
     */
    public function parseAliases($namespaceName, Tokenizer $t)
    {
        $t->expectToken(T_USE);
        $token = $t->getToken();

        if (isset($this->aliases[$namespaceName]) === false) {
            $this->aliases[$namespaceName] = array();
        }

        do {
            $token = $t->nextToken();
            $token = $t->parseName($fullName);

            if ($fullName[0] !== '\\') {
                $fullName = '\\' . $fullName;
            }

            if ($t->checkToken(T_AS)) {
                $token = $t->nextToken();
                $token = $t->parseName($alias);
            } else {
                $alias = substr(strrchr($fullName, '\\'), 1);
            }

            $this->aliases[$namespaceName][$alias] = $fullName;

        } while ($t->checkToken(','));

        $t->expectToken(';');

        return $t->nextToken();
    }

    /**
     * Translates given name to fully specified name
     *
     * @param  NamespaceReflectionInterface $fileName
     * @param  string $name
     * @return string
     */
    public function resolveName(NamespaceReflectionInterface $namespace, $name)
    {
        // fully classified name -> not an alias
        if ($name[0] === '\\') {
            return $name;
        }

        $namespaceName = $namespace->getName();

        // unknown alias / not an alias -> translate relative to fully classified name
        if (!isset($this->aliases[$namespaceName],
                $this->aliases[$namespaceName][$name])) {
            return $namespaceName . '\\' . $name;
        }

        // alias translation
        return $this->aliases[$namespaceName][$name];
    }

    /**
     * Returns reflection for given interface
     *
     * @param  NamespaceReflectionInterface $ns the current namespace
     * @param  string $name an interface name
     * @return InterfaceReflectionInterface
     *
     * @throws InvalidItemException
     */
    public function getInterface(NamespaceReflectionInterface $ns, $name)
    {
        $fullName = $this->resolveName($ns, $name);

        return $this->factory->getInterface($fullName);
    }

    /**
     * Returns reflection for given class
     *
     * @param  NamespaceReflectionInterface $ns the current namespace
     * @param  string $name fully qualified class name
     * @return ClassReflectionInterface
     *
     * @throws InvalidItemException
     */
    public function getClass(NamespaceReflectionInterface $ns, $name)
    {
        $fullName = $this->resolveName($ns, $name);

        return $this->factory->getClass($fullName);
    }
}
