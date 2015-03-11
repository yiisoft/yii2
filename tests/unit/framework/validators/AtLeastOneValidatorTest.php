<?php
namespace yiiunit\framework\validators;

use yii\validators\AtLeastOneValidator;
use yii\base\InvalidConfigException;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class AtLeastOneValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateAttributeAndError()
    {
        $model = new FakedValidationModel;

        $val = new AtLeastOneValidator(['validateWith' => 'attr2']);
        $model->attr1 = null;
        $model->attr2 = null;
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(2, $model->getErrors(), 'Model should have 2 error attributes.');

        $val = new AtLeastOneValidator(['validateWith' => 'attr2']);
        $model->attr1 = '';
        $model->attr2 = [];
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(2, $model->getErrors(), 'Model should have 2 error attributes.');

        $val = new AtLeastOneValidator([
            'validateWith' => 'attr2',
            'errorAttributes'=>'attr1',
        ]);
        $model->attr1 = null;
        $model->attr2 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(1, $model->getErrors(), 'Model should have 1 error attribute.');
        $this->assertCount(0, $model->getErrors('attr2'), 'Attr2 should have no error.');

        $val = new AtLeastOneValidator([
            'validateWith' => ['attr2', 'attr3'],
            'errorAttributes'=> ['attr2', 'attr3'],
        ]);
        $model->attr1 = null;
        $model->attr2 = null;
        $model->attr3 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(2, $model->getErrors(), 'Model should have 2 error attributes.');
        $this->assertCount(0, $model->getErrors('attr1'), 'Attr1 should have no error.');

        $val = new AtLeastOneValidator(['validateWith' => 'attr2']);
        $model->attr1 = 1;
        $model->attr2 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(0, $model->getErrors(), 'Model should have 0 error attributes.');

        $val = new AtLeastOneValidator(['validateWith' => 'attr2']);
        $model->attr1 = null;
        $model->attr2 = 1;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(0, $model->getErrors(), 'Model should have 0 error attributes.');

        $val = new AtLeastOneValidator(['validateWith' => 'attr2']);
        $model->attr1 = 1;
        $model->attr2 = 2;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(0, $model->getErrors(), 'Model should have 0 error attributes.');

        try {
            new AtLeastOneValidator();
            $this->fail('Exception should have been thrown at this time. Lack of the `validateWith` param.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('yii\base\InvalidConfigException', $e);
            $this->assertEquals('Param "validateWith" can not be null', $e->getMessage());
        }
    }
}
