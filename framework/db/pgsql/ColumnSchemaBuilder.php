<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\pgsql;

use yii\db\ColumnSchemaBuilder as AbstractColumnSchemaBuilder;

/**
 * ColumnSchemaBuilder is the schema builder for PostgreSQL databases.
 *
 * @author Dmytry Naumenko <d.naumenko.a@gmail.com>
 * @since 2.0.7
 */
class ColumnSchemaBuilder extends AbstractColumnSchemaBuilder
{
    /**
     * @inheritdoc
     */
    protected function buildDefaultString($alter = false)
    {
        $string = parent::buildDefaultString($alter);

        // https://github.com/yiisoft/yii2/issues/9903
        if ($string !== '' && $alter === true) {
            $string = ' SET' . $string;
        }

        return $string;
    }
}
