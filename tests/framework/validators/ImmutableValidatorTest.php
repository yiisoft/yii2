<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/11
 * Time: 下午7:29
 */

namespace yiiunit\framework\validators;

use yii\validators\ImmutableValidator;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\data\ar\ActiveRecord;

class ImmutableValidatorTest extends DatabaseTestCase
{
    protected function setUp()
    {
        $this->driverName = 'mysql';
        parent::setUp();

        // destroy application, Validator must work without Yii::$app

        $this->destroyApplication();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testValidateAttribute()
    {

        $validator = new ImmutableValidator();
        $model = ValidatorTestRefModel::findOne(['id' => 1]);
        $this->assertFalse($model->hasErrors('a_field'));
        $model->a_field = 'value_changed';
        $validator->validateAttribute($model,'a_field');
        $this->assertTrue($model->hasErrors('a_field'));
    }
}
