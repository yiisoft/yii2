<?php

/**
 * Creates a call for the method `yii\db\Migration::dropTable()`.
 */
/* @var $table string the name table */
/* @var $foreignKeys array the foreign keys */

echo $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]) ?>
        $this->dropTable('<?= $table ?>');
