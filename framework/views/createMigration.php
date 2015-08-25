<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $primaryKey string the primary key */
/* @var $createAt string */
/* @var $updateAt string */

echo "<?php\n";
?>

use yii\db\Migration;

class <?= $className ?> extends Migration
{
    public function up()
    {
        $this->createTable('<?= $table ?>', [
            '<?= $primaryKey ?>' => $this->primaryKey(),
<?php foreach ($fields as $field): ?>
            '<?= $field['property'] ?>' => $this-><?= $field['decorators'] . ",\n"?>
<?php endforeach; ?>
            '<?= $createAt ?>' => $this->integer(),
            '<?= $updateAt ?>' => $this->integer()
        ]);
    }

    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
