<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\validators\models;

use yii\base\Model;

class ValidatorTestFunctionModel extends Model
{
    public $firstAttribute;

    public function required()
    {
        return true;
    }
}
