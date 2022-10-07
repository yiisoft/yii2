<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\validators\models;

use yii\base\Model;

class ValidatorTestEachAndInlineMethodModel extends Model
{
    public $arrayProperty = [true, false];

    public function rules()
    {
        return [
            ['arrayProperty', 'each', 'rule' => [function ($attribute, $params, $validator) {
                if (is_array($this->$attribute)) {
                    $this->addError($attribute, 'Each & Inline validators bug');
                }
            }]],
        ];
    }
}
