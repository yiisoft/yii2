<?php

echo  $this->render('_dropForeignKeys', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
    'tName' => $tName,
]);

foreach ($fields as $field): ?>
        $this->dropColumn('<?= $tName($table) ?>', '<?= $field['property'] ?>');
<?php endforeach;
