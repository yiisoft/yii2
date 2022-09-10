<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
