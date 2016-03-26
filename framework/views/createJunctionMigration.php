<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */
/* @var $table string the name table */
/* @var $field_first string the name field first */
/* @var $field_second string the name field second */

echo "<?php\n";
?>

use yii\db\Migration;

/**
 * Handles the creation of table `<?= $table ?>` which is a junction between
 * table `<?= $field_first ?>` and table `<?= $field_second ?>`.
 */
class <?= $className ?> extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('<?= $table ?>', [
            '<?= $field_first ?>_id' => $this->integer(),
            '<?= $field_second ?>_id' => $this->integer(),
            'PRIMARY KEY(<?= $field_first ?>_id, <?= $field_second ?>_id)',
        ]);

        $this->createIndex(
            'idx-<?= $table . '-' . $field_first ?>_id',
            '<?= $table ?>',
            '<?= $field_first ?>_id'
        );

        $this->createIndex(
            'idx-<?= $table . '-' . $field_second ?>_id',
            '<?= $table ?>',
            '<?= $field_second ?>_id'
        );

        $this->addForeignKey(
            'fk-<?= $table . '-' . $field_first ?>_id',
            '<?= $table ?>',
            '<?= $field_first ?>_id',
            '<?= $field_first ?>',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-<?= $table . '-' . $field_second ?>_id',
            '<?= $table ?>',
            '<?= $field_second ?>_id',
            '<?= $field_second ?>',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
