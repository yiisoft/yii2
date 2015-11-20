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
<?php foreach ($fields as $field): ?>
        $this->addColumn(<?= "'$table', '" . $field['property'] . "', \$this->" . $field['decorators'] ?>);
<?php endforeach; ?>
    }

    public function down()
    {
<?php foreach ($fields as $field): ?>
        $this->dropColumn(<?= "'$table', '" . $field['property'] . "'" ?>);
<?php endforeach; ?>
    }
}
