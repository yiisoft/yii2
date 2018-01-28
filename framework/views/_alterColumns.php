<?php foreach ($fields as $field): ?>
        $this->alterColumn('<?=
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

