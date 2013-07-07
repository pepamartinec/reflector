<?php
namespace Reflector\NodeVisitor;

use PHPParser_Node;

class FilenameNodeVisitor extends \PHPParser_NodeVisitorAbstract {

    /**
     * @var string|null
     */
    private $filename = null;

    /**
     * Sets current filename.
     *
     * @param $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Sets filename to the node.
     *
     * @param PHPParser_Node $node
     */
    public function enterNode(PHPParser_Node $node) {
        $node->filename = $this->filename;
    }

    /**
     * Resets filename after each traversal.
     *
     * @param array $nodes
     */
    public function afterTraverse(array $nodes) {
        $this->filename = null;
    }
}
