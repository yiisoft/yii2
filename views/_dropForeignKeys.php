<?php foreach ($foreignKeys as $column => $relatedTable): ?>
        // drops foreign key for table `<?= $relatedTable ?>`
        $this->dropForeignKey(
            '<?= "fk-$table-$column" ?>',
            '<?= $table ?>'
        );

        // drops index for column `<?= $column ?>`
        $this->dropIndex(
            '<?= "idx-$table-$column" ?>',
            '<?= $table ?>'
        );

<?php endforeach;
