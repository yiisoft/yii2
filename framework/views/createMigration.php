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

class <?= $className ?> extends Migration
{
    public function up()
    {
        $this->createTable('<?= $table ?>', [
<?php foreach ($fields as $field): ?>
<?php if ($field == end($fields)): ?>
            '<?= $field['property'] ?>' => $this-><?= $field['decorators'] . "\n"?>
<?php else: ?>
            '<?= $field['property'] ?>' => $this-><?= $field['decorators'] . ",\n"?>
<?php endif; ?>
<?php endforeach; ?>
        ]);
    }

    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
