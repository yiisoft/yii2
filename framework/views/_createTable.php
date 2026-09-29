<?php

/**
 * Creates a call for the method `yii\db\Migration::createTable()`.
 *
 * @var \yii\web\View $this
 * @var string $table the name table
 * @var array $fields the fields
 * @var array $foreignKeys the foreign keys
 */

?>        $this->createTable('<?= $table ?>', [
<?php foreach ($fields as $field):
    if (empty($field['decorators'])): ?>
            '<?= $field['property'] ?>',
<?php else: ?>
            <?= "'{$field['property']}' => \$this->{$field['decorators']}" ?>,
<?php endif;
endforeach; ?>
        ]);
<?= $this->render('_addForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);
