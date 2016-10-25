<?php

namespace yiiunit\data\validators\models;

use yii\base\Model;
use yiiunit\data\validators\CommonErrorsValidator;

class FakedValidationModel extends Model
{
    public $val_attr_a;
    public $val_attr_b;
    public $val_attr_c;
    public $val_attr_d;
    private $attr = [];

    /**
     * @param  array $attributes
     * @return self
     */
    public static function createWithAttributes($attributes = [])
    {
        $m = new static();
        foreach ($attributes as $attribute => $value) {
            $m->$attribute = $value;
        }

        return $m;
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), array_keys($this->attr));
    }

    public function rules()
    {
        return [
            [['val_attr_a', 'val_attr_b'], 'required', 'on' => 'reqTest'],
            ['val_attr_c', 'integer'],
            ['attr_images', 'file', 'maxFiles' => 3, 'extensions' => ['png'], 'on' => 'validateMultipleFiles', 'checkExtensionByMimeType' => false],
            ['attr_image', 'file', 'extensions' => ['png'], 'on' => 'validateFile', 'checkExtensionByMimeType' => false],
            [null, CommonErrorsValidator::className(), 'on' => 'validateCommonWithValidator'],
            [null, 'validateCommon', 'on' => 'validateCommonWithMethod'],
        ];
    }

    public function inlineVal($attribute, $params = [])
    {
        return true;
    }

    public function validateCommon($model)
    {
        $this->addError(null, 'Common error!');
        $this->addError(null, 'Another common error!');
    }

    public function __get($name)
    {
        if (stripos($name, 'attr') === 0) {
            return isset($this->attr[$name]) ? $this->attr[$name] : null;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (stripos($name, 'attr') === 0) {
            $this->attr[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function getAttributeLabel($attr)
    {
        return $attr;
    }
}
