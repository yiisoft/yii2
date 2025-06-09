<?php

/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 *
 * @var string $className the new migration class name without namespace
 * @var string $namespace the new migration class namespace
 * @var string $table the name table
 * @var string $tableComment the comment table
 * @var array $fields the fields
 * @var array $foreignKeys the foreign keys
 */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use yii\db\Migration;

/**
 * Handles the creation of table `<?= $table ?>`.
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
<?= $this->render('_createTable', [
    'table' => $table,
    'fields' => $fields,
    'foreignKeys' => $foreignKeys,
])
?>
<?php if (!empty($tableComment)) {
    echo $this->render('_addComments', [
        'table' => $table,
        'tableComment' => $tableComment,
    ]);
}
?>
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
<?= $this->render('_dropTable', [
    'table' => $table,
    'foreignKeys' => $foreignKeys,
])
?>
    }
}
