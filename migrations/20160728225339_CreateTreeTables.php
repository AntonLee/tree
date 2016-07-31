<?php

use Phpmig\Migration\Migration;

class CreateTreeTables extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $tableName = $container['config']['table-name'];

        $sql = <<<SQL
CREATE TABLE `$tableName` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;

        $container['db']->exec($sql);

        $sql = <<<SQL
CREATE TABLE `{$tableName}_paths` (
  `ancestor` int(10) NOT NULL,
  `descendant` int(10) NOT NULL,
  `length` smallint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ancestor`,`descendant`),
  FOREIGN KEY (`ancestor`) 
  REFERENCES `tree` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (`descendant`) 
  REFERENCES `tree` (`id`) 
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $container['db']->exec($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
        $tableName = $container['config']['table-name'];

        $sql = <<<SQL
DROP TABLE `{$tableName}_paths`;
SQL;
        $container['db']->exec($sql);

        $sql = <<<SQL
DROP TABLE `$tableName`;
SQL;

        $container['db']->exec($sql);
    }
}
