<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * @property int $attr1
 * @property int $attr2
 */
class NoAutoLabels extends ActiveRecord
{
    public function attributeLabels()
    {
        return [
            'attr1' => 'Label for attr1',
        ];
    }

    public function generateAttributeLabel($name)
    {
        throw new \yii\base\InvalidArgumentException('Label not defined!');
    }
}
