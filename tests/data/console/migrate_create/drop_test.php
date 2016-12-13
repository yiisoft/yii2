<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the dropping of table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropTable('{table}');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->createTable('{table}', [
            'id' => \$this->primaryKey(),
        ]);
    }
}

CODE;
