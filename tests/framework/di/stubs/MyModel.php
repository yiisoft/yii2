<?php

namespace yiiunit\framework\di\stubs;

use yii\base\Model;
use yii\validators\EmailValidator;

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

    public function rules()
    {
        return[
            [['email'], 'customValidator'],
        ];
    }

    public function customValidator($attribute, $params, EmailValidator $validator)
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
