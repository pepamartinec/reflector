<?php
namespace Reflector\NodeVisitor;

use PHPParser_Node;
use Reflector\Reflection\Code\StaticClassReflection;
use Reflector\Reflection\Code\StaticInterfaceReflection;
use Reflector\ReflectionRegistry;

class ConstructingNodeVisitor extends \PHPParser_NodeVisitorAbstract {

    /**
     * @var ReflectionRegistry
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param ReflectionRegistry $registry
     */
    public function __construct(ReflectionRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param PHPParser_Node $node
     */
    public function enterNode(PHPParser_Node $node)
    {
        if ($node instanceof \PHPParser_Node_Stmt_Class) {
            new StaticClassReflection($node, $this->registry);

        } elseif ($node instanceof \PHPParser_Node_Stmt_Interface) {
            new StaticInterfaceReflection($node, $this->registry);
        }
    }
}
