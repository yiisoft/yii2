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
            'title' => \$this->string(10)->notNull()->unique()->defaultValue("test"),
            'body' => \$this->text()->notNull(),
            'price' => \$this->money(11,2)->notNull(),
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
