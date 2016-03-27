<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */
/* @var $table string the name table */
/* @var $fields array the fields */

echo "<?php\n";
?>

use yii\db\Migration;

/**
 * Handles the creation for table `<?= $table ?>`.
 */
class <?= $className ?> extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('<?= $table ?>', [
<?php foreach ($fields as $field): ?>
            <?= "'{$field['property']}' => \$this->{$field['decorators']}" ?>,
<?php endforeach; ?>
        ]);
<?php foreach ($foreignKeys as $column => $relatedTable): ?>

        // index for column `<?= $column ?>`
        $this->createIndex(
            '<?= "idx-$table-$column" ?>',
            '<?= $table ?>',
            '<?= $column ?>'
        );

        // foreign key for table `<?= $relatedTable ?>`
        $this->createIndex(
            '<?= "fk-$table-$column" ?>',
            '<?= $table ?>',
            '<?= $column ?>',
            '<?= $relatedTable ?>',
            'id',
            'CASCADE'
        );
<?php endforeach; ?>
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
