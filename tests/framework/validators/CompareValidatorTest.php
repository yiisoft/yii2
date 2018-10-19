<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class CompareValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testValidateValueException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $val = new CompareValidator();
        $val->validate('val');
    }

    public function testValidateValue()
    {
        $value = 18449;
        // default config
        $val = new CompareValidator(['compareValue' => $value]);
        $this->assertTrue($val->validate($value));
        $this->assertTrue($val->validate((string) $value));
        $this->assertFalse($val->validate($value + 1));
        foreach ($this->getOperationTestData($value) as $op => $tests) {
            $val = new CompareValidator(['compareValue' => $value]);
            $val->operator = $op;
            foreach ($tests as $test) {
                $this->assertEquals($test[1], $val->validate($test[0]), "Testing $op");
            }
        }
    }

    protected function getOperationTestData($value)
    {
        return [
            '===' => [
                [$value, true],
                [(string) $value, true],
                [(float) $value, true],
                [$value + 1, false],
            ],
            '!=' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [$value + 0.00001, true],
                [false, true],
            ],
            '!==' => [
                [$value, false],
                [(string) $value, false],
                [(float) $value, false],
                [false, true],
            ],
            '>' => [
                [$value, false],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '>=' => [
                [$value, true],
                [$value + 1, true],
                [$value - 1, false],
            ],
            '<' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, true],
            ],
            '<=' => [
                [$value, true],
                [$value + 1, false],
                [$value - 1, true],
            ],
            /*'non-op' => [
                [$value, false],
                [$value + 1, false],
                [$value - 1, false],
            ],*/
        ];
    }

    public function testValidateAttribute()
    {
        // invalid-array
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr = ['test_val'];
        $val->validateAttribute($model, 'attr');
        $this->assertTrue($model->hasErrors('attr'));
        $val = new CompareValidator(['compareValue' => 'test-string']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        $val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_val = 'test-string-false';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_val'));
        // assume: _repeat
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string';
        $val->validateAttribute($model, 'attr_test');
        $this->assertFalse($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        $val = new CompareValidator();
        $model = new FakedValidationModel();
        $model->attr_test = 'test-string';
        $model->attr_test_repeat = 'test-string2';
        $val->validateAttribute($model, 'attr_test');
        $this->assertTrue($model->hasErrors('attr_test'));
        $this->assertFalse($model->hasErrors('attr_test_repeat'));
        // not existing op
        $val = new CompareValidator();
        $val->operator = '<>';
        $model = FakedValidationModel::createWithAttributes(['attr_o' => 5, 'attr_o_repeat' => 5]);
        $val->validateAttribute($model, 'attr_o');
        $this->assertTrue($model->hasErrors('attr_o'));
    }

    public function testAttributeErrorMessages()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr1' => 1,
            'attr2' => 2,
            'attrN' => 2,
        ]);

        foreach ($this->getTestDataForMessages() as $data) {
            $model->clearErrors($data[0]);
            $validator = new CompareValidator();
            $validator->operator = $data[1];
            $validator->message = null;
            $validator->init(); // reload messages
            $validator->{$data[4]} = $data[2];
            $validator->validateAttribute($model, $data[0]);
            $error = $model->getErrors($data[0])[0];
            $this->assertEquals($data[3], $error);
        }
    }

    protected function getTestDataForMessages()
    {
        return [
            ['attr1', '==', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attr1', '===', 2, 'attr1 must be equal to "2".', 'compareValue'],
            ['attrN', '!=', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attrN', '!==', 2, 'attrN must not be equal to "2".', 'compareValue'],
            ['attr1', '>', 2, 'attr1 must be greater than "2".', 'compareValue'],
            ['attr1', '>=', 2, 'attr1 must be greater than or equal to "2".', 'compareValue'],
            ['attr2', '<', 1, 'attr2 must be less than "1".', 'compareValue'],
            ['attr2', '<=', 1, 'attr2 must be less than or equal to "1".', 'compareValue'],

            ['attr1', '==', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attr1', '===', 'attr2', 'attr1 must be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!=', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attrN', '!==', 'attr2', 'attrN must not be equal to "attr2".', 'compareAttribute'],
            ['attr1', '>', 'attr2', 'attr1 must be greater than "attr2".', 'compareAttribute'],
            ['attr1', '>=', 'attr2', 'attr1 must be greater than or equal to "attr2".', 'compareAttribute'],
            ['attr2', '<', 'attr1', 'attr2 must be less than "attr1".', 'compareAttribute'],
            ['attr2', '<=', 'attr1', 'attr2 must be less than or equal to "attr1".', 'compareAttribute'],
        ];
    }

    public function testValidateAttributeOperators()
    {
        $value = 55;
        foreach ($this->getOperationTestData($value) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator, 'compareValue' => $value]);
            foreach ($tests as $test) {
                $model = new FakedValidationModel();
                $model->attr_test = $test[0];
                $val->validateAttribute($model, 'attr_test');
                $this->assertEquals($test[1], !$model->hasErrors('attr_test'));
            }
        }
    }

    public function testEnsureMessageSetOnInit()
    {
        foreach ($this->getOperationTestData(1337) as $operator => $tests) {
            $val = new CompareValidator(['operator' => $operator]);
            $this->assertTrue(strlen($val->message) > 1);
        }
        try {
            new CompareValidator(['operator' => '<>']);
        } catch (InvalidConfigException $e) {
            return;
        } catch (\Exception $e) {
            $this->fail('InvalidConfigException expected' . get_class($e) . 'received');

            return;
        }
        $this->fail('InvalidConfigException expected none received');
    }
}
