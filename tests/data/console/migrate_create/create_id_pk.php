<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'address' => \$this->string(),
            'address2' => \$this->string(),
            'email' => \$this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('test');
    }
}

CODE;
