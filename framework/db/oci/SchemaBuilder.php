<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db\ocl;


use yii\db\SchemaBuilder as AbstractSchemaBuilder;

class SchemaBuilder extends AbstractSchemaBuilder
{
    public function __toString()
    {
        return
            $this->schema .
            $this->getLengthString() .
            $this->getDefaultString() .
            $this->getNullString() .
            $this->getCheckString();
    }
}