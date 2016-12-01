<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('{table}', [
            'brand_id' => \$this->bigPrimaryKey()->unsigned(),
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
