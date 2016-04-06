<?php foreach ($foreignKeys as $column => $relatedTable): ?>

        // creates index for column `<?= $column ?>`
        $this->createIndex(
            '<?= $tName("idx-$table-$column") ?>',
            '<?= $tName($table) ?>',
            '<?= $column ?>'
        );

        // add foreign key for table `<?= $tName($relatedTable) ?>`
        $this->addForeignKey(
            '<?= $tName("fk-$table-$column") ?>',
            '<?= $tName($table) ?>',
            '<?= $column ?>',
            '<?= $tName($relatedTable) ?>',
            'id',
            'CASCADE'
        );
<?php endforeach;
