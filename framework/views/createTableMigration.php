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
            <?= "'{$field['property']}' => \$this->{$field['decorators']},\n" ?>
<?php endforeach; ?>
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
