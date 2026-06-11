<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators\stubs;

use yii\base\Model;

class ModelForStringValidator extends Model
{
    public $attr;

    public function attributeLabels()
    {
        return ['attr' => 'Test Attribute'];
    }
}
