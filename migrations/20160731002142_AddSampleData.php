<?php

use Phpmig\Migration\Migration;

class AddSampleData extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
        $tree = new \AntonLee\Tree($container['config']['table-name'], $container['db']);
        $treeBuilder = new \AntonLee\TreeHelper($tree);
        $treeBuilder->addSubTree(array(
            array(
                'content' => 'A',
                'items' => array(
                    array(
                        'content' => 'B',
                        'items' => array(
                            'D',
                            'X',
                            'Y',
                            'Z',
                        )
                    ),
                    array(
                        'content' => 'C',
                        'items' => array(
                            'E',
                            array(
                                'content' => 'F',
                                'items' => array(
                                    'G'
                                )
                            ),
                            'H',
                        )
                    ),
                    'a',
                    'b',
                    'c',
                )
            )
        ), 'content', 'items');
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
