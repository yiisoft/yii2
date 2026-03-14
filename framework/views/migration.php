<?php

/**
 * This view is used by console/controllers/MigrateController.php.
 *
 * The following variables are available in this view:
 *
 * @var string $className the new migration class name without namespace
 * @var string $namespace the new migration class namespace
 */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}
?>

use yii\db\Migration;

class <?= $className ?> extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "<?= $className ?> cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "<?= $className ?> cannot be reverted.\n";

        return false;
    }
    */
}
