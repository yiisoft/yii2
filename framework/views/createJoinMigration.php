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

class <?= $className ?> extends Migration
{
    public function up()
    {
        $this->createTable('<?= $table ?>', [
            '<?= $field_first ?>' => $this->integer(),
            '<?= $field_second ?>' => $this->integer(),
            'PRIMARY KEY(<?= $field_first ?>, <?= $field_second ?>)'
        ]);
    }

    public function down()
    {
        $this->dropTable('<?= $table ?>');
    }
}
