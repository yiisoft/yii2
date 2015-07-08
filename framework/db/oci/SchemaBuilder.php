<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\oci;

use yii\db\SchemaBuilder as AbstractSchemaBuilder;

/**
 * SchemaBuilder is the schema builder for Oracle databases.
 *
 * @author Vasenin Matvey <vaseninm@gmail.com>
 * @since 2.0.5
 */
class SchemaBuilder extends AbstractSchemaBuilder
{
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return
            $this->type .
            $this->buildLengthString() .
            $this->buildDefaultString() .
            $this->buildNotNullString() .
            $this->buildCheckString();
    }
}
