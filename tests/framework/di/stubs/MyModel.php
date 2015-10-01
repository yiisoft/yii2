<?php

namespace yiiunit\framework\di\stubs;

use yii\base\Model;
use yii\validators\EmailValidator;
use yii\validators\StringValidator;

/**
 * Description of MyModel
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class MyModel extends Model
{
    public $name;
    public $email;
    public $qux;

    public function rules()
    {
        return[
            [['name'], 'inlineValidator1'],
            [['email'], 'inlineValidator2'],
            [['qux'], 'default', 'value' => function(Bar $bar){
                return $bar->qux;
            }],
        ];
    }

    public function inlineValidator1(StringValidator $validator)
    {
        if(!$validator->validate($this->name)){
            $this->addError('name', 'Is not string');
        }
    }

    public function inlineValidator2($attribute, $params, EmailValidator $validator)
    {
        if(!$validator->validate($this->$attribute)){
            $this->addError($attribute, 'Email Invalid');
        }
    }

    public function test($param, QuxInterface $qux, EmailValidator $validator)
    {
        return [$param, $qux, $validator];
    }

    
}
