<?php

/**
 * @var array $foreignKeys
 * @var string $table
 */

?>
<?php foreach ($foreignKeys as $column => $fkData): ?>
        // drops foreign key for table `<?= $fkData['relatedTable'] ?>`
        $this->dropForeignKey(
            '<?= $fkData['fk'] ?>',
            '<?= $table ?>'
        );

        // drops index for column `<?= $column ?>`
        $this->dropIndex(
            '<?= $fkData['idx'] ?>',
            '<?= $table ?>'
        );

<?php endforeach;
