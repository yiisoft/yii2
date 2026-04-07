<?php

/**
 * Creates a call for the method `yii\db\Migration::dropTable()`.
 *
 * @var \yii\web\View $this
 * @var string $table the name table
 * @var array $foreignKeys the foreign keys
 */

echo $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]) ?>
        $this->dropTable('<?= $table ?>');
