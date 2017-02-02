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
            'id' => \$this->primaryKey(),
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
