<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->addColumn('{table}', 'title', \$this->string(10)->notNull());
        \$this->addColumn('{table}', 'body', \$this->text()->notNull());
        \$this->addColumn('{table}', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('{table}', 'created_at', \$this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropColumn('{table}', 'title');
        \$this->dropColumn('{table}', 'body');
        \$this->dropColumn('{table}', 'price');
        \$this->dropColumn('{table}', 'created_at');
    }
}

CODE;
