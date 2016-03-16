<?php
namespace yiiunit\data\base;

use Yii;
use yii\base\Model;

/**
 * Model with point notations
 */
class Embed extends Model
{
    protected $_options;
    public function getOptions()
    {
        return $this->_options ? unserialize($this->_options) : new Options;
    }
    public function setOptions($array)
    {
        $options = Yii::configure(new Options, $array);
        $this->_options = serialize($options);
    }
    public function attributeLabels()
    {
        return [
            'options.firstName' => 'Option First Name',
        ];
    }
    public function rules()
    {
        return [
            [['options.firstName', 'options.lastName'], 'required'],
        ];
    }
    public function attributes()
    {
        return ['options'];
    }
}
use \yii\base\Object;
class Options extends Object {
    public $firstName;
    public $lastName = 'Lennon';
}