<?php

require_once 'vendor/autoload.php';

$config = require '.config.php';

$dbh = new PDO("mysql:dbname={$config['db-name']};host={$config['host']}", $config['username'], $config['password']);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$tree = new \AntonLee\Tree($config['table-name'], $dbh);

$result = 'void';
if (isset($_REQUEST['action'])) {
    try {
        switch ($_REQUEST['action']) {
            case 'add':
                $result = $tree->addNode($_REQUEST['content'], $_REQUEST['parent-id']);
                break;
            case 'get':
                $result = $tree->getNode($_REQUEST['id']);
                break;
            case 'update':
                $result = $tree->updateNode($_REQUEST['id'], $_REQUEST['content']);
                break;
            case 'delete':
                $tree->deleteNode($_REQUEST['id']);
                break;
            case 'roots':
                $result = $tree->getRoots();
                break;
            case 'sub-tree':
                $result = $tree->getSubTree($_REQUEST['id']);
                break;
            case 'breadcrumbs':
                $result = $tree->getBreadcrumbs($_REQUEST['id']);
                break;
            case 'move-tree':
                $tree->moveSubTree($_REQUEST['id'], $_REQUEST['parent-id']);
                break;
        }
    } catch (\Exception $e) {
        $result = $e->getMessage();
    }
}

$treeHelper = new \AntonLee\TreeHelper($tree);
foreach ($tree->getRoots() as $root) {
    $arrayTree = $treeHelper->getTree($root);
    Kint::dump($arrayTree);
    echo $treeHelper->prettyPrint($arrayTree);
}
?>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<form method="post" class="form-inline">
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1">
            <div class="form-group">
                <label for="id">id:</label>
                <input type="number" name="id" id="id">
            </div>
            <div class="form-group">
                <label for="parent-id">parentId:</label>
                <input type="number" name="parent-id" id="parent-id">
            </div>
            <div class="form-group">
                <label for="content">content:</label>
                <input type="text" name="content" id="content">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-10 col-xs-offset-1">
            <button name="action" value="add" class="btn btn-default">addNode($content, $parentId)</button>
            <button name="action" value="get" class="btn btn-default">getNode($id)</button>
            <button name="action" value="update" class="btn btn-default">updateNode($id, $content)</button>
            <button name="action" value="delete" class="btn btn-default">deleteNode($id)</button>
            <button name="action" value="roots" class="btn btn-default">getRoots()</button>
            <button name="action" value="sub-tree" class="btn btn-default">getSubTree($id)</button>
            <button name="action" value="breadcrumbs" class="btn btn-default">getBreadcrumbs($id)</button>
            <button name="action" value="move-tree" class="btn btn-default">moveSubTree($id, $parentId)</button>
        </div>
    </div>
</form>
<div>
    <?php
    if (isset($_REQUEST['action'])) {
        Kint::dump($result);
    }
    ?>
</div>
