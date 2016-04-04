<?php foreach ($foreignKeys as $column => $relatedTable): ?>

        // creates index for column `<?= $column ?>`
        $this->createIndex(
            '<?= "idx-$table-$column" ?>',
            '<?= $table ?>',
            '<?= $column ?>'
        );

        // add foreign key for table `<?= $relatedTable ?>`
        $this->addForeignKey(
            '<?= "fk-$table-$column" ?>',
            '<?= $table ?>',
            '<?= $column ?>',
            '<?= $relatedTable ?>',
            'id',
            'CASCADE'
        );
<?php endforeach;
