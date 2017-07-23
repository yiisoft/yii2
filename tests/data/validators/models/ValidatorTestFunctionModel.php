<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
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