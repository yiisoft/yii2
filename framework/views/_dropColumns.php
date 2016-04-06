<?php

echo  $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
]);

foreach ($fields as $field): ?>
        $this->dropColumn('<?= $table ?>', '<?= $field['property'] ?>');
<?php endforeach;
