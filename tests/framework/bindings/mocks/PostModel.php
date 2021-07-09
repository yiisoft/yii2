<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\bindings\mocks;

class PostModel extends \yii\base\Model
{
    public $title = "";
    public $content = "";

    public $findOneCalled = false;
    public $setAttributesCalled = false;
    public $arguments = null;

    public function setAttributes($values, $safeOnly = true)
    {
        $this->setAttributesCalled = true;
        $this->arguments = [
            'values' => $values,
            'safeOnly' => $safeOnly
        ];
        parent::setAttributes($values, $safeOnly);
    }

    public function rules()
    {
        return [
            ['title', 'string'],
            ['content', 'string'],
        ];
    }
}
