<?php
namespace Reflector;

use Reflector\NodeVisitor\ConstructingNodeVisitor;
use Reflector\NodeVisitor\FilenameNodeVisitor;
use Reflector\Reflection\ClassReflectionInterface;
use Reflector\Reflection\InterfaceReflectionInterface;
use Reflector\Reflection\NamespaceReflectionInterface;

class Reflector
{
    /**
     * @var ReflectionRegistry
     */
    protected $registry;

    /**
     * @var \PHPParser_Parser
     */
    private $parser;

    /**
     * @var \PHPParser_NodeTraverser
     */
    private $traverser;

    /**
     * @var FilenameNodeVisitor
     */
    private $filenameVisitor;

    /**
     * Constructs new ReflectionFactory
     */
    public function __construct()
    {
        $this->registry  = new ReflectionRegistry();
        $this->parser    = new \PHPParser_Parser();
        $this->traverser = new \PHPParser_NodeTraverser();

        $this->filenameVisitor = new FilenameNodeVisitor();

        $this->traverser->addVisitor(new \PHPParser_NodeVisitor_NameResolver());
        $this->traverser->addVisitor($this->filenameVisitor);
        $this->traverser->addVisitor(new ConstructingNodeVisitor($this->registry));
    }

    /**
     * Analyzes whole directory
     *
     * @param string $dirName
     * @param bool   $recursive
     *
     * @throws InvalidFileException
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

        /** @var $item \SplFileInfo */
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
     * Analyzes single file.
     *
     * @param string $filename
     *
     * @throws InvalidFileException
     */
    public function analyzeFile($filename)
    {
        if (!is_file($filename)) {
            throw new InvalidFileException($filename);
        }

        $this->filenameVisitor->setFilename($filename);

        $this->analyzeCode(file_get_contents($filename));
    }

    /**
     * Analyzes source code.
     *
     * @param string $code
     */
    public function analyzeCode($code)
    {
        $lexer = new \PHPParser_Lexer_Emulative($code);

        try {
            $stmts = $this->parser->parse($lexer);
            $stmts = $this->traverser->traverse($stmts);

        } catch (\PHPParser_Error $e) {
            // TODO
        }
    }

    /**
     * Returns namespace reflection
     *
     * @param  string $name fully qualified namespace name
     * @return NamespaceReflectionInterface|null
     */
    public function getNamespace($name)
    {
        if ($this->registry->hasNamespace($name)) {
            return $this->registry->getNamespace($name);

        } else {
            return null;
        }
    }

    /**
     * Returns reflection for given interface
     *
     * @param  string $name fully qualified interface name
     * @return InterfaceReflectionInterface|null
     */
    public function getInterface($name)
    {
        if ($this->registry->hasInterface($name)) {
            return $this->registry->getInterface($name);

        } else {
            return null;
        }
    }

    /**
     * Returns reflection for given class
     *
     * @param  string $name fully qualified class name
     * @return ClassReflectionInterface|null
     */
    public function getClass($name)
    {
        if ($this->registry->hasClass($name)) {
            return $this->registry->getClass($name);

        } else {
            return null;
        }
    }
}
