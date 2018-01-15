<?php
/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 */
/* @var $className string the new migration class name without namespace */
/* @var $namespace string the new migration class namespace */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $oldFields array the old fields or empty if the old fields must be ignored */

preg_match('/^alter_(.+)_columns?_of_(.+)_table$/', $name, $matches);
$columns = $matches[1];

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use yii\db\Migration;

/**
 * Handles changing columns '<?= $columns ?>' of table `<?= $table ?>`.
<?= $this->render('_foreignTables', [
     'foreignKeys' => $foreignKeys,
 ]) ?>
 */
class <?= $className ?> extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
<?= $this->render('_alterColumns', [
    'table' => $table,
    'fields' => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
<?= $this->render('_alterColumns', [
    'table' => $table,
    'fields' => $oldFields,
    'foreignKeys' => $foreignKeys,
])
?>
    }
}

