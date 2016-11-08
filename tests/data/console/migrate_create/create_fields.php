<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation for table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('{table}', [
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
        \$this->dropTable('{table}');
    }
}

CODE;
