<?php

namespace AntonLee;

class TreeHelper
{
    private $childrenKeyName = 'CHILDREN';
    private $tree;

    /**
     * TreeHelper constructor.
     *
     * @param Tree $tree
     */
    public function __construct(Tree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @param array $subTree - array of nodes
     * @param $contentKeyName - key name of node array element that contains node content
     * @param $childrenKeyName - key name of node array element that contains subNodes
     * @param int $parentId
     *
     * @return int|string - id of created sub tree root
     * @throws \Exception
     */
    public function addSubTree(array $subTree, $contentKeyName, $childrenKeyName, $parentId = 0)
    {
        if (!is_array($subTree)) {
            throw new \Exception('sub tree must be an array');
        }
        $id = 0;
        foreach ($subTree as $node) {
            if (is_array($node)) {
                $id = $this->tree->addNode($node[$contentKeyName], $parentId);
                if (isset($node[$childrenKeyName])) {
                    $this->addSubTree($node[$childrenKeyName], $contentKeyName, $childrenKeyName, $id);
                }
            } else {
                $id = $this->tree->addNode($node, $parentId);
            }
        }

        return $id;
    }

    /**
     * @param int $treeRootId
     *
     * @return array
     */
    public function getTree($treeRootId = null)
    {
        if (is_null($treeRootId)) {
            $treeRootId = reset($this->tree->getRoots());
        }
        $treeNodes = $this->tree->getSubTree($treeRootId);
        $children = array();
        $tree = array();
        $treeRootParentId = '';
        foreach ($treeNodes as $node) {
            if ($node['id'] == $treeRootId) {
                $treeRootParentId = $node['parent_id'];
            }
            $children[$node['parent_id']][] = $node;
        }

        return $this->getChildren($treeRootParentId, $children);
    }

    private function getChildren($rootNodeId, $children)
    {
        $nodeChildren = array();
        if (isset($children[$rootNodeId])) {
            foreach ($children[$rootNodeId] as $child) {
                $childChildren = $this->getChildren($child['id'], $children);
                if (count($childChildren) > 0) {
                    $child[$this->childrenKeyName] = $childChildren;
                }
                $nodeChildren[] = $child;
            }
        }

        return $nodeChildren;
    }

    public function prettyPrint(array $tree)
    {
        $result = '';
        foreach ($tree as $node) {
            $result .= sprintf(
                '<li>(%03d) %s%s</li>',
                $node['id'],
                $node['content'],
                (isset($node[$this->childrenKeyName])?$this->prettyPrint($node[$this->childrenKeyName]):'')
            );
        }
        return '<ul>'.$result.'</ul>';
    }
}
