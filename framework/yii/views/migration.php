<?php
/**
 * This view is used by console/controllers/MigrateController.php
 * The following variables are available in this view:
 *
 * @var string $className the new migration class name
 */
echo "<?php\n";
?>

use yii\db\Schema;

class <?= $className ?> extends \yii\db\Migration
{
public function up()
{

}

public function down()
{
echo "<?= $className ?> cannot be reverted.\n";
return false;
}
}
