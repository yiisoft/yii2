<?php
namespace yiiunit\framework\validators;

use yii\validators\AtLeastValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class AtLeastValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testAtLeastOne()
    {
        $model = new FakedValidationModel;

        $val = new AtLeastValidator(['attributes' => 'attr1', 'alternativeAttributes' => ['attr2']]);
        $model->attr1 = 1;
        $model->attr2 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertEquals([], $model->getErrors(), 'Model should have 0 error attributes.');

        $val = new AtLeastValidator(['attributes' => ['attr1', 'attr2']]);
        $model->attr1 = null;
        $model->attr2 = 1;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertEquals([], $model->getErrors(), 'Model should have 0 error attributes.');
    }

    public function testAtLeastMin()
    {
        $model = new FakedValidationModel;

        $val = new AtLeastValidator([
            'attributes' => 'attr1',
            'alternativeAttributes' => ['attr2', 'attr3'],
            'min' => 2,
        ]);
        $model->attr1 = 1;
        $model->attr2 = null;
        $model->attr3 = 3;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertEquals([], $model->getErrors(), 'Model should have 0 error attributes.');

        $val = new AtLeastValidator([
            'attributes' => ['attr1', 'attr2', 'attr3'],
            'min' => 2,
        ]);
        $model->attr1 = 1;
        $model->attr2 = null;
        $model->attr3 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(1, $model->getErrors('attr1'));
        $this->assertSame('At least 2 inputs of "attr1" or "attr2" or "attr3" must be filled.', $model->getFirstError('attr1'));
    }

    public function testErrorAttributes()
    {
        $model = new FakedValidationModel;

        $val = new AtLeastValidator(['attributes' => 'attr1', 'alternativeAttributes' => ['attr2']]);
        $model->attr1 = null;
        $model->attr2 = null;
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(1, $model->getErrors('attr1'));
        $this->assertCount(1, $model->getErrors('attr2'));
        $this->assertSame('At least one input of "attr1" or "attr2" must be filled.', $model->getFirstError('attr1'));
        $this->assertSame('At least one input of "attr1" or "attr2" must be filled.', $model->getFirstError('attr2'));

        $val = new AtLeastValidator([
            'attributes' => 'attr1',
            'alternativeAttributes' => ['attr2'],
            'errorAttributes' => ['attr1'],
        ]);
        $model->attr1 = null;
        $model->attr2 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(1, $model->getErrors('attr1'));
        $this->assertSame('At least one input of "attr1" or "attr2" must be filled.', $model->getFirstError('attr1'));
        $this->assertSame([], $model->getErrors('attr2'), 'Attr2 should have no error.');

        $val = new AtLeastValidator([
            'attributes' => ['attr1', 'attr2', 'attr3'],
            'min' => 2,
            'errorAttributes' => ['attr2', 'attr3'],
        ]);
        $model->attr1 = 1;
        $model->attr2 = null;
        $model->attr3 = null;
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(0, $model->getErrors('attr1'));
        $this->assertCount(1, $model->getErrors('attr2'));
        $this->assertCount(1, $model->getErrors('attr3'));
        $this->assertSame('At least 2 inputs of "attr1" or "attr2" or "attr3" must be filled.', $model->getFirstError('attr2'));
        $this->assertSame('At least 2 inputs of "attr1" or "attr2" or "attr3" must be filled.', $model->getFirstError('attr3'));

        $model = new FakedValidationModel;
        $model->setScenario('atLeastTest');
        $model->val_attr_a = null;
        $model->val_attr_b = null;
        $model->val_attr_c = null;
        $model->clearErrors();
        $model->validate();
        $this->assertCount(0, $model->getErrors('val_attr_a'));
        $this->assertCount(1, $model->getErrors('val_attr_b'));
        $this->assertCount(1, $model->getErrors('val_attr_c'));
        $this->assertSame('At least one input of "val_attr_a" or "val_attr_b" or "val_attr_c" must be filled.', $model->getFirstError('val_attr_b'));
        $this->assertSame('At least one input of "val_attr_a" or "val_attr_b" or "val_attr_c" must be filled.', $model->getFirstError('val_attr_c'));
    }

    public function testEmptyValues()
    {
        $model = new FakedValidationModel;

        $val = new AtLeastValidator(['attributes' => 'attr1', 'alternativeAttributes' => ['attr2']]);
        $model->attr1 = '';
        $model->attr2 = [];
        $model->clearErrors();
        $val->validateAttribute($model, 'attr1');
        $this->assertCount(1, $model->getErrors('attr1'));
        $this->assertCount(1, $model->getErrors('attr2'));
        $this->assertSame('At least one input of "attr1" or "attr2" must be filled.', $model->getFirstError('attr1'));
        $this->assertSame('At least one input of "attr1" or "attr2" must be filled.', $model->getFirstError('attr2'));
    }
}
