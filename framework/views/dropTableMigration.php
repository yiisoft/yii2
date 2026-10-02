<?php

/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 *
 * @var \yii\web\View $this
 * @var string $className the new migration class name without namespace
 * @var string $namespace the new migration class namespace
 * @var string $table the name table
 * @var array $fields the fields
 * @var array $foreignKeys
 */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use yii\db\Migration;

/**
 * Handles the dropping of table `<?= $table ?>`.
<?= $this->render('_foreignTables', [
    'foreignKeys' => $foreignKeys,
]) ?>
 */
class <?= $className ?> extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
<?= $this->render('_dropTable', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
])
?>
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
<?= $this->render('_createTable', [
    'table' => $table,
    'fields' => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
    }
}
