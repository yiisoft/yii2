<?php

namespace yiiunit\framework\base;

use yii\base\Model;
use yiiunit\data\base\InvalidRulesModel;
use yiiunit\data\base\RulesModel;
use yiiunit\data\base\Singer;
use yiiunit\data\base\Speaker;
use yiiunit\TestCase;

/**
 * @group base
 */
class ModelTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testGetAttributeLabel()
    {
        $speaker = new Speaker();
        $this->assertEquals('First Name', $speaker->getAttributeLabel('firstName'));
        $this->assertEquals('This is the custom label', $speaker->getAttributeLabel('customLabel'));
        $this->assertEquals('Underscore Style', $speaker->getAttributeLabel('underscore_style'));
    }

    public function testGetAttributes()
    {
        $speaker = new Speaker();
        $speaker->firstName = 'Qiang';
        $speaker->lastName = 'Xue';

        $this->assertEquals([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
            'customLabel' => null,
            'underscore_style' => null,
        ], $speaker->getAttributes());

        $this->assertEquals([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
        ], $speaker->getAttributes(['firstName', 'lastName']));

        $this->assertEquals([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
        ], $speaker->getAttributes(null, ['customLabel', 'underscore_style']));

        $this->assertEquals([
            'firstName' => 'Qiang',
        ], $speaker->getAttributes(['firstName', 'lastName'], ['lastName', 'customLabel', 'underscore_style']));
    }

    public function testSetAttributes()
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();
        $speaker->setAttributes(['firstName' => 'Qiang', 'underscore_style' => 'test']);
        $this->assertNull($speaker->firstName);
        $this->assertNull($speaker->underscore_style);

        // in the test scenario
        $speaker = new Speaker();
        $speaker->setScenario('test');
        $speaker->setAttributes(['firstName' => 'Qiang', 'underscore_style' => 'test']);
        $this->assertNull($speaker->underscore_style);
        $this->assertEquals('Qiang', $speaker->firstName);

        $speaker->setAttributes(['firstName' => 'Qiang', 'underscore_style' => 'test'], false);
        $this->assertEquals('test', $speaker->underscore_style);
        $this->assertEquals('Qiang', $speaker->firstName);
    }

    public function testLoad()
    {
        $singer = new Singer();
        $this->assertEquals('Singer', $singer->formName());

        $post = ['firstName' => 'Qiang'];

        Speaker::$formName = '';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertTrue($model->load($post));
        $this->assertEquals('Qiang', $model->firstName);

        Speaker::$formName = 'Speaker';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertTrue($model->load(['Speaker' => $post]));
        $this->assertEquals('Qiang', $model->firstName);

        Speaker::$formName = 'Speaker';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertFalse($model->load(['Example' => []]));
        $this->assertEquals('', $model->firstName);
    }

    public function testActiveAttributes()
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();
        $this->assertEmpty($speaker->activeAttributes());

        $speaker = new Speaker();
        $speaker->setScenario('test');
        $this->assertEquals(['firstName', 'lastName', 'underscore_style'], $speaker->activeAttributes());
    }

    public function testIsAttributeSafe()
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();
        $this->assertFalse($speaker->isAttributeSafe('firstName'));

        $speaker = new Speaker();
        $speaker->setScenario('test');
        $this->assertTrue($speaker->isAttributeSafe('firstName'));

    }

    public function testSafeScenarios()
    {
        $model = new RulesModel();
        $model->rules = [
            // validated and safe on every scenario
            [['account_id', 'user_id'], 'required'],
        ];
        $model->scenario = Model::SCENARIO_DEFAULT;
        $this->assertEquals(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update'; // not exsisting scenario
        $this->assertEquals([], $model->safeAttributes());
        $this->assertEquals([], $model->activeAttributes());

        $model = new RulesModel();
        $model->rules = [
            // validated and safe on every scenario
            [['account_id', 'user_id'], 'required'],
            // only in create and update scenario
            [['user_id'], 'number', 'on' => ['create', 'update']],
            [['email', 'name'], 'required', 'on' => 'create']
        ];
        $model->scenario = Model::SCENARIO_DEFAULT;
        $this->assertEquals(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update';
        $this->assertEquals(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'create';
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());

        $model = new RulesModel();
        $model->rules = [
            // validated and safe on every scenario
            [['account_id', 'user_id'], 'required'],
            // only in create and update scenario
            [['user_id'], 'number', 'on' => ['create', 'update']],
            [['email', 'name'], 'required', 'on' => 'create'],
            [['email', 'name'], 'required', 'on' => 'update'],
        ];
        $model->scenario = Model::SCENARIO_DEFAULT;
        $this->assertEquals(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update';
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());
        $model->scenario = 'create';
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertEquals(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());
    }

    public function testUnsafeAttributes()
    {
        $model = new RulesModel();
        $model->rules = [
            [['name', '!email'], 'required'], // Name is safe to set, but email is not. Both are required
        ];
        $this->assertEquals(['name'], $model->safeAttributes());
        $this->assertEquals(['name', 'email'], $model->activeAttributes());
        $model->attributes = ['name' => 'mdmunir', 'email' => 'mdm@mun.com'];
        $this->assertNull($model->email);
        $this->assertFalse($model->validate());

        $model = new RulesModel();
        $model->rules = [
            [['name'], 'required'],
            [['!user_id'], 'default', 'value' => '3426'],
        ];
        $model->attributes = ['name' => 'mdmunir', 'user_id' => '62792684'];
        $this->assertTrue($model->validate());
        $this->assertEquals('3426', $model->user_id);

        $model = new RulesModel();
        $model->rules = [
            [['name', 'email'], 'required'],
            [['!email'], 'safe']
        ];
        $this->assertEquals(['name'], $model->safeAttributes());
        $model->attributes = ['name' => 'mdmunir', 'email' => 'm2792684@mdm.com'];
        $this->assertFalse($model->validate());

        $model = new RulesModel();
        $model->rules = [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['!email'], 'safe', 'on' => 'update']
        ];
        $model->setScenario(RulesModel::SCENARIO_DEFAULT);
        $this->assertEquals(['name', 'email'], $model->safeAttributes());
        $model->attributes = ['name' => 'mdmunir', 'email' => 'm2792684@mdm.com'];
        $this->assertTrue($model->validate());

        $model->setScenario('update');
        $this->assertEquals(['name'], $model->safeAttributes());
        $model->attributes = ['name' => 'D426', 'email' => 'd426@mdm.com'];
        $this->assertNotEquals('d426@mdm.com', $model->email);
    }

    public function testErrors()
    {
        $speaker = new Speaker();

        $this->assertEmpty($speaker->getErrors());
        $this->assertEmpty($speaker->getErrors('firstName'));
        $this->assertEmpty($speaker->getFirstErrors());

        $this->assertFalse($speaker->hasErrors());
        $this->assertFalse($speaker->hasErrors('firstName'));

        $speaker->addError('firstName', 'Something is wrong!');
        $this->assertEquals(['firstName' => ['Something is wrong!']], $speaker->getErrors());
        $this->assertEquals(['Something is wrong!'], $speaker->getErrors('firstName'));

        $speaker->addError('firstName', 'Totally wrong!');
        $this->assertEquals(['firstName' => ['Something is wrong!', 'Totally wrong!']], $speaker->getErrors());
        $this->assertEquals(['Something is wrong!', 'Totally wrong!'], $speaker->getErrors('firstName'));

        $this->assertTrue($speaker->hasErrors());
        $this->assertTrue($speaker->hasErrors('firstName'));
        $this->assertFalse($speaker->hasErrors('lastName'));

        $this->assertEquals(['firstName' => 'Something is wrong!'], $speaker->getFirstErrors());
        $this->assertEquals('Something is wrong!', $speaker->getFirstError('firstName'));
        $this->assertNull($speaker->getFirstError('lastName'));

        $speaker->addError('lastName', 'Another one!');
        $this->assertEquals([
            'firstName' => [
                'Something is wrong!',
                'Totally wrong!',
            ],
            'lastName' => ['Another one!'],
        ], $speaker->getErrors());

        $speaker->clearErrors('firstName');
        $this->assertEquals([
            'lastName' => ['Another one!'],
        ], $speaker->getErrors());

        $speaker->clearErrors();
        $this->assertEmpty($speaker->getErrors());
        $this->assertFalse($speaker->hasErrors());
    }

    public function testAddErrors()
    {
        $singer = new Singer();

        $errors = ['firstName' => ['Something is wrong!']];
        $singer->addErrors($errors);
        $this->assertEquals($singer->getErrors(), $errors);

        $singer->clearErrors();
        $singer->addErrors(['firstName' => 'Something is wrong!']);
        $this->assertEquals($singer->getErrors(), ['firstName' => ['Something is wrong!']]);

        $singer->clearErrors();
        $errors = ['firstName' => ['Something is wrong!', 'Totally wrong!']];
        $singer->addErrors($errors);
        $this->assertEquals($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!'],
            'lastName' => ['Another one!']
        ];
        $singer->addErrors($errors);
        $this->assertEquals($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!', 'Totally wrong!'],
            'lastName' => ['Another one!']
        ];
        $singer->addErrors($errors);
        $this->assertEquals($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!', 'Totally wrong!'],
            'lastName' => ['Another one!', 'Totally wrong!']
        ];
        $singer->addErrors($errors);
        $this->assertEquals($singer->getErrors(), $errors);
    }

    public function testArraySyntax()
    {
        $speaker = new Speaker();

        // get
        $this->assertNull($speaker['firstName']);

        // isset
        $this->assertFalse(isset($speaker['firstName']));

        // set
        $speaker['firstName'] = 'Qiang';

        $this->assertEquals('Qiang', $speaker['firstName']);
        $this->assertTrue(isset($speaker['firstName']));

        // iteration
        $attributes = [];
        foreach ($speaker as $key => $attribute) {
            $attributes[$key] = $attribute;
        }
        $this->assertEquals([
            'firstName' => 'Qiang',
            'lastName' => null,
            'customLabel' => null,
            'underscore_style' => null,
        ], $attributes);

        // unset
        unset($speaker['firstName']);

        // exception isn't expected here
        $this->assertNull($speaker['firstName']);
        $this->assertFalse(isset($speaker['firstName']));
    }

    public function testDefaults()
    {
        $singer = new Model();
        $this->assertEquals([], $singer->rules());
        $this->assertEquals([], $singer->attributeLabels());
    }

    public function testDefaultScenarios()
    {
        $singer = new Singer();
        $this->assertEquals(['default' => ['lastName', 'underscore_style', 'test']], $singer->scenarios());

        $scenarios = [
            'default' => ['id', 'name', 'description'],
            'administration' => ['name', 'description', 'is_disabled'],
        ];
        $model = new ComplexModel1();
        $this->assertEquals($scenarios, $model->scenarios());
        $scenarios = [
            'default' => ['id', 'name', 'description'],
            'suddenlyUnexpectedScenario' => ['name', 'description'],
            'administration' => ['id', 'name', 'description', 'is_disabled'],
        ];
        $model = new ComplexModel2();
        $this->assertEquals($scenarios, $model->scenarios());
    }

    public function testIsAttributeRequired()
    {
        $singer = new Singer();
        $this->assertFalse($singer->isAttributeRequired('firstName'));
        $this->assertTrue($singer->isAttributeRequired('lastName'));

        // attribute is not marked as required when a conditional validation is applied using `$when`.
        // the condition should not be applied because this info may be retrieved before model is loaded with data
        $singer->firstName = 'qiang';
        $this->assertFalse($singer->isAttributeRequired('test'));
        $singer->firstName = 'cebe';
        $this->assertFalse($singer->isAttributeRequired('test'));
    }

    public function testCreateValidators()
    {
        $this->setExpectedException('yii\base\InvalidConfigException',
            'Invalid validation rule: a rule must specify both attribute names and validator type.');

        $invalid = new InvalidRulesModel();
        $invalid->createValidators();
    }
}

class ComplexModel1 extends Model
{
    public function rules()
    {
        return [
            [['id'], 'required', 'except' => 'administration'],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
            [['is_disabled'], 'boolean', 'on' => 'administration'],
        ];
    }
}

class ComplexModel2 extends Model
{
    public function rules()
    {
        return [
            [['id'], 'required', 'except' => 'suddenlyUnexpectedScenario'],
            [['name', 'description'], 'filter', 'filter' => 'trim'],
            [['is_disabled'], 'boolean', 'on' => 'administration'],
        ];
    }
}
