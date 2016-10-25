<?php
namespace yiiunit\data\validators;

use yii\validators\Validator;

class CommonErrorsValidator extends Validator
{
    public function validateModel($model)
    {
        $this->addError($model, null, 'Common error!');
        $this->addError($model, null, 'Another common error!');
    }
}
