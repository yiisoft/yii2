<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropTable('test');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'body' => \$this->text()->notNull(),
            'price' => \$this->money(11,2),
        ]);
    }
}

CODE;
