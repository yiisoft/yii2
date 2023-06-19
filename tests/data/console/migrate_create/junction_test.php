<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

return <<<CODE
<?php

{$namespace}use yii\db\Migration;

/**
 * Handles the creation of table `{{%{junctionTable}}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%{firstTable}}}`
 * - `{{%{secondTable}}}`
 */
class {$class} extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \$this->createTable('{{%{junctionTable}}}', [
            '{firstTable}_id' => \$this->integer(),
            '{secondTable}_id' => \$this->integer(),
            'PRIMARY KEY({firstTable}_id, {secondTable}_id)',
        ]);

        // creates index for column `{firstTable}_id`
        \$this->createIndex(
            '{{%idx-{junctionTable}-{firstTable}_id}}',
            '{{%{junctionTable}}}',
            '{firstTable}_id'
        );

        // add foreign key for table `{{%{firstTable}}}`
        \$this->addForeignKey(
            '{{%fk-{junctionTable}-{firstTable}_id}}',
            '{{%{junctionTable}}}',
            '{firstTable}_id',
            '{{%{firstTable}}}',
            'id',
            'CASCADE'
        );

        // creates index for column `{secondTable}_id`
        \$this->createIndex(
            '{{%idx-{junctionTable}-{secondTable}_id}}',
            '{{%{junctionTable}}}',
            '{secondTable}_id'
        );

        // add foreign key for table `{{%{secondTable}}}`
        \$this->addForeignKey(
            '{{%fk-{junctionTable}-{secondTable}_id}}',
            '{{%{junctionTable}}}',
            '{secondTable}_id',
            '{{%{secondTable}}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%{firstTable}}}`
        \$this->dropForeignKey(
            '{{%fk-{junctionTable}-{firstTable}_id}}',
            '{{%{junctionTable}}}'
        );

        // drops index for column `{firstTable}_id`
        \$this->dropIndex(
            '{{%idx-{junctionTable}-{firstTable}_id}}',
            '{{%{junctionTable}}}'
        );

        // drops foreign key for table `{{%{secondTable}}}`
        \$this->dropForeignKey(
            '{{%fk-{junctionTable}-{secondTable}_id}}',
            '{{%{junctionTable}}}'
        );

        // drops index for column `{secondTable}_id`
        \$this->dropIndex(
            '{{%idx-{junctionTable}-{secondTable}_id}}',
            '{{%{junctionTable}}}'
        );

        \$this->dropTable('{{%{junctionTable}}}');
    }
}

CODE;
