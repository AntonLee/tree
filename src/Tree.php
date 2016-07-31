<?php

namespace AntonLee;

use PDO;

class Tree
{
    private $tableName;
    private $db;

    /**
     * Tree constructor.
     *
     * @param $tableName
     * @param PDO $db
     */
    public function __construct($tableName, PDO $db)
    {
        $this->tableName = $tableName;
        $this->db = $db;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getNode($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE id=:id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $nodeData = $stmt->fetch();

        return $nodeData;
    }

    /**
     * @return array
     */
    public function getRoots()
    {
        $roots = array();
        $query = $this->db->query("SELECT t.id FROM {$this->tableName} AS t"
            ." LEFT OUTER JOIN `{$this->tableName}_paths` AS p"
            ." ON t.id = p.descendant AND p.length = 1"
            ." WHERE p.ancestor IS NULL");
        while ($root = $query->fetchColumn()) {
            $roots[] = $root;
        }

        return $roots;
    }

    /**
     * @param $content
     * @param int $parentId
     *
     * @return string node id
     */
    public function addNode($content, $parentId = 0)
    {
        $db = $this->db;
        $tableName = $this->tableName;

        //insert node
        $sql = "INSERT INTO `$tableName` (content)"
            .'VALUES (:content)';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
        $id = $db->lastInsertId();
        $links[] = array(
            'ancestor' => $id,
            'length' => 0,
        );

        //insert links for node
        $sql = "INSERT INTO `{$this->tableName}_paths` (ancestor, descendant, length)"
            ." SELECT ancestor, :id, length + 1 FROM `{$this->tableName}_paths`"
            ." WHERE descendant = :parent_id"
            ." UNION ALL"
            ." SELECT :id, :id, 0";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $id;
    }

    /**
     * @param $id
     * @param $content
     *
     * @return bool
     */
    public function updateNode($id, $content)
    {
        $sql = "UPDATE `{$this->tableName}` SET content=:content WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get sub tree including node with $id.
     *
     * @param $id
     *
     * @return array
     */
    public function getSubTree($id)
    {
        $sql = "SELECT t.*, parent.ancestor as parent_id, p.length FROM `{$this->tableName}` AS t"
            ." JOIN `{$this->tableName}_paths` as p"
            ." ON t.id = p.descendant"
            ." LEFT OUTER JOIN `{$this->tableName}_paths` as parent"
            ." ON parent.descendant = t.id AND parent.length = 1"
            ." WHERE p.ancestor=:id"
            ." ORDER BY p.length ASC"
        ;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get node breadcrumbs including the node itself.
     *
     * @param $id
     *
     * @return array
     */
    public function getBreadcrumbs($id)
    {
        $sql = "SELECT t.*, parent.ancestor as parent_id, p.length FROM `{$this->tableName}` AS t"
            ." JOIN `{$this->tableName}_paths` as p"
            ." ON t.id = p.ancestor"
            ." LEFT OUTER JOIN `{$this->tableName}_paths` as parent"
            ." ON parent.descendant = t.id AND parent.length = 1"
            ." WHERE p.descendant=:id"
            ." ORDER BY p.length ASC"
        ;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete node with its descendants.
     *
     * @param $id
     */
    public function deleteNode($id)
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->tableName}` WHERE id=?");
        foreach ($this->getSubTree($id) as $node) {
            $stmt->execute(array(intval($node['id'])));
        }
    }

    /**
     * @param $id - node id
     * @param $parentId - new parent
     * @throws \Exception
     */
    public function moveSubTree($id, $parentId)
    {
        foreach ($this->getSubTree($id) as $node) {
            if ($node['id'] == $parentId) {
                throw new \Exception('parent should not be in the moved sub tree');
            }
        }

        $sql = "DELETE a FROM `{$this->tableName}_paths` AS a"
            ." JOIN `{$this->tableName}_paths` AS d ON a.descendant = d.descendant"
            ." LEFT JOIN `{$this->tableName}_paths` AS x"
            ." ON x.ancestor = d.ancestor AND x.descendant = a.ancestor"
            ." WHERE d.ancestor = :id AND x.ancestor IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $sql = "INSERT INTO `{$this->tableName}_paths` (ancestor, descendant, length)"
            ." SELECT supertree.ancestor, subtree.descendant,"
            ." supertree.length + subtree.length + 1"
            ." FROM `{$this->tableName}_paths` AS supertree"
            ." JOIN `{$this->tableName}_paths` AS subtree"
            ." WHERE subtree.ancestor = :id"
            ." AND supertree.descendant = :parent_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
