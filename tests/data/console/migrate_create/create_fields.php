<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `test`.
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
            'parenthesis_in_comment' => \$this->string(255)->notNull()->comment('Name of set (RU)'),
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
