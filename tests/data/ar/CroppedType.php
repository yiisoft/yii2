<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Model representing 2 columns from "type" table.
 *
 * @property int $int_col
 * @property int $int_col2 DEFAULT 1
 */
class CroppedType extends ActiveRecord
{
    /**
     * @inheritDoc
     */
    public static function tableName()
    {
        return '{{%type}}';
    }

    /**
     * @inheritDoc
     */
    public function attributes()
    {
        return ['int_col', 'int_col2'];
    }
}
