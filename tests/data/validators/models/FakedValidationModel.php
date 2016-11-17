<?php

namespace yiiunit\data\validators\models;

use yii\base\Model;

class FakedValidationModel extends Model
{
    public $val_attr_a;
    public $val_attr_b;
    public $val_attr_c;
    public $val_attr_d;
    private $attr = [];
    private $inlineValArgs;

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

    public function rules()
    {
        return [
            [['val_attr_a', 'val_attr_b'], 'required', 'on' => 'reqTest'],
            ['val_attr_c', 'integer'],
            ['attr_images', 'file', 'maxFiles' => 3, 'extensions' => ['png'], 'on' => 'validateMultipleFiles', 'checkExtensionByMimeType' => false],
            ['attr_image', 'file', 'extensions' => ['png'], 'on' => 'validateFile', 'checkExtensionByMimeType' => false]
        ];
    }

    public function inlineVal($attribute, $params = [], $validator)
    {
        $this->inlineValArgs = func_get_args();

        return true;
    }

    public function clientInlineVal($attribute, $params = [], $validator)
    {
        return func_get_args();
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

    /**
     * Get arguments of inlineVal method in the last call.
     * @return array|null an array of arguments in the last call or null if method never been called.
     * @see inlineVal
     */
    public function getInlineValArgs()
    {
        return $this->inlineValArgs;
    }
}
