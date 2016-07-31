<?php

use \Phpmig\Adapter;

$config = require '.config.php';

$container = new ArrayObject();

$dbh = new PDO("mysql:dbname={$config['db-name']};host={$config['host']}", $config['username'], $config['password']);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$container['db'] = $dbh;
$container['config'] = $config;
$container['phpmig.adapter'] = new Adapter\PDO\Sql($container['db'], 'migrations');
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
