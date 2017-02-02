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
            'title' => \$this->string(10)->notNull()->unique()->defaultValue(",te,st"),
            'body' => \$this->text()->notNull()->defaultValue(",test"),
            'test' => \$this->custom(11,2,"s")->notNull(),
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
