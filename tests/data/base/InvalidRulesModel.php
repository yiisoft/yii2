<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

use yii\base\Model;

/**
 * InvalidRulesModel.
 */
class InvalidRulesModel extends Model
{
    public function rules()
    {
        return [
            ['test'],
        ];
    }
}
