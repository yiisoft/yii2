<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\validators\models;

use yii\base\Model;

class FakedValidationModel extends Model
{
    public $val_attr_a;
    public $val_attr_b;
    public $val_attr_c;
    public $val_attr_d;
    public $safe_attr;
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
            ['attr_image', 'file', 'extensions' => ['png'], 'on' => 'validateFile', 'checkExtensionByMimeType' => false],
            ['!safe_attr', 'integer'],
        ];
    }

    public function inlineVal($attribute, $params, $validator, $current)
    {
        $this->inlineValArgs = \func_get_args();

        return true;
    }

    public function clientInlineVal($attribute, $params, $validator, $current, $view = null)
    {
        return \func_get_args();
    }

    public function __get($name)
    {
        if (strncasecmp($name, 'attr', 4) === 0) {
            return isset($this->attr[$name]) ? $this->attr[$name] : null;
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (strncasecmp($name, 'attr', 4) === 0) {
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
     * Returns the arguments of the inlineVal method in the last call.
     * @return array|null an array of arguments in the last call or null if method never been called.
     * @see inlineVal
     */
    public function getInlineValArgs()
    {
        return $this->inlineValArgs;
    }

    public function attributes()
    {
        return array_keys($this->attr);
    }
}
