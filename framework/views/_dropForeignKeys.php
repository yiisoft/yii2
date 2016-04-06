<?php foreach ($foreignKeys as $column => $relatedTable): ?>
        // drops foreign key for table `<?= $tName($relatedTable) ?>`
        $this->dropForeignKey(
            '<?= $tName("fk-$table-$column")?>',
            '<?= $tName($table) ?>'
        );

        // drops index for column `<?= $column ?>`
        $this->dropIndex(
            '<?= $tName("idx-$table-$column")?>',
            '<?= $tName($table) ?>'
        );

<?php endforeach;
