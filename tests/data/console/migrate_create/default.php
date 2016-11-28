<?php

return <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function up()
    {

    }

    public function down()
    {
        echo "{$class} cannot be reverted.\\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}

CODE;
