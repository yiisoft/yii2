<?php

/**
 * @var \yii\web\View $this
 * @var array $fields
 * @var string $table
 * @var array $foreignKeys
 */

?>
<?php foreach ($fields as $field): ?>
        $this->addColumn('<?=
            $table
        ?>', '<?=
            $field['property']
        ?>', $this-><?=
            $field['decorators']
        ?>);
<?php endforeach;

echo $this->render('_addForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);
