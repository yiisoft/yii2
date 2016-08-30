<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `products_from_store`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropTable('products_from_store');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->createTable('products_from_store', [
            'id' => \$this->primaryKey(),
        ]);
    }
}

CODE;
