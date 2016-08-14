<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `products_from_store`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('products_from_store', [
            'id' => \$this->primaryKey(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('products_from_store');
    }
}

CODE;
