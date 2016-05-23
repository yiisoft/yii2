<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `test`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->addColumn('test', 'title', \$this->string(10)->notNull());
        \$this->addColumn('test', 'body', \$this->text()->notNull());
        \$this->addColumn('test', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('test', 'created_at', \$this->dateTime());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropColumn('test', 'title');
        \$this->dropColumn('test', 'body');
        \$this->dropColumn('test', 'price');
        \$this->dropColumn('test', 'created_at');
    }
}

CODE;
