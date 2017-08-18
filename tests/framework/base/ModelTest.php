<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
        $this->assertSame('First Name', $speaker->getAttributeLabel('firstName'));
        $this->assertSame('This is the custom label', $speaker->getAttributeLabel('customLabel'));
        $this->assertSame('Underscore Style', $speaker->getAttributeLabel('underscore_style'));
    }

    public function testGetAttributes()
    {
        $speaker = new Speaker();
        $speaker->firstName = 'Qiang';
        $speaker->lastName = 'Xue';

        $this->assertSame([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
            'customLabel' => null,
            'underscore_style' => null,
        ], $speaker->getAttributes());

        $this->assertSame([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
        ], $speaker->getAttributes(['firstName', 'lastName']));

        $this->assertSame([
            'firstName' => 'Qiang',
            'lastName' => 'Xue',
        ], $speaker->getAttributes(null, ['customLabel', 'underscore_style']));

        $this->assertSame([
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
        $this->assertSame('Qiang', $speaker->firstName);

        $speaker->setAttributes(['firstName' => 'Qiang', 'underscore_style' => 'test'], false);
        $this->assertSame('test', $speaker->underscore_style);
        $this->assertSame('Qiang', $speaker->firstName);
    }

    public function testLoad()
    {
        $singer = new Singer();
        $this->assertSame('Singer', $singer->formName());

        $post = ['firstName' => 'Qiang'];

        Speaker::$formName = '';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertTrue($model->load($post));
        $this->assertSame('Qiang', $model->firstName);

        Speaker::$formName = 'Speaker';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertTrue($model->load(['Speaker' => $post]));
        $this->assertSame('Qiang', $model->firstName);

        Speaker::$formName = 'Speaker';
        $model = new Speaker();
        $model->setScenario('test');
        $this->assertFalse($model->load(['Example' => []]));
        $this->assertSame('', $model->firstName);
    }

    public function testLoadMultiple()
    {
        $data = [
            ['firstName' => 'Thomas', 'lastName' => 'Anderson'],
            ['firstName' => 'Agent', 'lastName' => 'Smith'],
        ];

        Speaker::$formName = '';
        $neo = new Speaker();
        $neo->setScenario('test');
        $smith = new Speaker();
        $smith->setScenario('test');
        $this->assertTrue(Speaker::loadMultiple([$neo, $smith], $data));
        $this->assertSame('Thomas', $neo->firstName);
        $this->assertSame('Smith', $smith->lastName);

        Speaker::$formName = 'Speaker';
        $neo = new Speaker();
        $neo->setScenario('test');
        $smith = new Speaker();
        $smith->setScenario('test');
        $this->assertTrue(Speaker::loadMultiple([$neo, $smith], ['Speaker' => $data], 'Speaker'));
        $this->assertSame('Thomas', $neo->firstName);
        $this->assertSame('Smith', $smith->lastName);

        Speaker::$formName = 'Speaker';
        $neo = new Speaker();
        $neo->setScenario('test');
        $smith = new Speaker();
        $smith->setScenario('test');
        $this->assertFalse(Speaker::loadMultiple([$neo, $smith], ['Speaker' => $data], 'Morpheus'));
        $this->assertSame('', $neo->firstName);
        $this->assertSame('', $smith->lastName);
    }

    public function testActiveAttributes()
    {
        // by default mass assignment doesn't work at all
        $speaker = new Speaker();
        $this->assertEmpty($speaker->activeAttributes());

        $speaker = new Speaker();
        $speaker->setScenario('test');
        $this->assertSame(['firstName', 'lastName', 'underscore_style'], $speaker->activeAttributes());
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
        $this->assertSame(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update'; // not existing scenario
        $this->assertSame([], $model->safeAttributes());
        $this->assertSame([], $model->activeAttributes());

        $model = new RulesModel();
        $model->rules = [
            // validated and safe on every scenario
            [['account_id', 'user_id'], 'required'],
            // only in create and update scenario
            [['user_id'], 'number', 'on' => ['create', 'update']],
            [['email', 'name'], 'required', 'on' => 'create'],
        ];
        $model->scenario = Model::SCENARIO_DEFAULT;
        $this->assertSame(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update';
        $this->assertSame(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'create';
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());

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
        $this->assertSame(['account_id', 'user_id'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id'], $model->activeAttributes());
        $model->scenario = 'update';
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());
        $model->scenario = 'create';
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->safeAttributes());
        $this->assertSame(['account_id', 'user_id', 'email', 'name'], $model->activeAttributes());
    }

    public function testUnsafeAttributes()
    {
        $model = new RulesModel();
        $model->rules = [
            [['name', '!email'], 'required'], // Name is safe to set, but email is not. Both are required
        ];
        $this->assertSame(['name'], $model->safeAttributes());
        $this->assertSame(['name', 'email'], $model->activeAttributes());
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
        $this->assertSame('3426', $model->user_id);

        $model = new RulesModel();
        $model->rules = [
            [['name', 'email'], 'required'],
            [['!email'], 'safe'],
        ];
        $this->assertSame(['name'], $model->safeAttributes());
        $model->attributes = ['name' => 'mdmunir', 'email' => 'm2792684@mdm.com'];
        $this->assertFalse($model->validate());

        $model = new RulesModel();
        $model->rules = [
            [['name', 'email'], 'required'],
            [['email'], 'email'],
            [['!email'], 'safe', 'on' => 'update'],
        ];
        $model->setScenario(RulesModel::SCENARIO_DEFAULT);
        $this->assertSame(['name', 'email'], $model->safeAttributes());
        $model->attributes = ['name' => 'mdmunir', 'email' => 'm2792684@mdm.com'];
        $this->assertTrue($model->validate());

        $model->setScenario('update');
        $this->assertSame(['name'], $model->safeAttributes());
        $model->attributes = ['name' => 'D426', 'email' => 'd426@mdm.com'];
        $this->assertNotSame('d426@mdm.com', $model->email);
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
        $this->assertSame(['firstName' => ['Something is wrong!']], $speaker->getErrors());
        $this->assertSame(['Something is wrong!'], $speaker->getErrors('firstName'));

        $speaker->addError('firstName', 'Totally wrong!');
        $this->assertSame(['firstName' => ['Something is wrong!', 'Totally wrong!']], $speaker->getErrors());
        $this->assertSame(['Something is wrong!', 'Totally wrong!'], $speaker->getErrors('firstName'));

        $this->assertTrue($speaker->hasErrors());
        $this->assertTrue($speaker->hasErrors('firstName'));
        $this->assertFalse($speaker->hasErrors('lastName'));

        $this->assertSame(['firstName' => 'Something is wrong!'], $speaker->getFirstErrors());
        $this->assertSame('Something is wrong!', $speaker->getFirstError('firstName'));
        $this->assertNull($speaker->getFirstError('lastName'));

        $speaker->addError('lastName', 'Another one!');
        $this->assertSame([
            'firstName' => [
                'Something is wrong!',
                'Totally wrong!',
            ],
            'lastName' => ['Another one!'],
        ], $speaker->getErrors());

        $speaker->clearErrors('firstName');
        $this->assertSame([
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
        $this->assertSame($singer->getErrors(), $errors);

        $singer->clearErrors();
        $singer->addErrors(['firstName' => 'Something is wrong!']);
        $this->assertSame($singer->getErrors(), ['firstName' => ['Something is wrong!']]);

        $singer->clearErrors();
        $errors = ['firstName' => ['Something is wrong!', 'Totally wrong!']];
        $singer->addErrors($errors);
        $this->assertSame($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!'],
            'lastName' => ['Another one!'],
        ];
        $singer->addErrors($errors);
        $this->assertSame($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!', 'Totally wrong!'],
            'lastName' => ['Another one!'],
        ];
        $singer->addErrors($errors);
        $this->assertSame($singer->getErrors(), $errors);

        $singer->clearErrors();
        $errors = [
            'firstName' => ['Something is wrong!', 'Totally wrong!'],
            'lastName' => ['Another one!', 'Totally wrong!'],
        ];
        $singer->addErrors($errors);
        $this->assertSame($singer->getErrors(), $errors);
    }

    public function testArraySyntax()
    {
        $speaker = new Speaker();

        // get
        $this->assertNull($speaker['firstName']);

        // isset
        $this->assertFalse(isset($speaker['firstName']));
        $this->assertFalse(isset($speaker['unExistingField']));

        // set
        $speaker['firstName'] = 'Qiang';

        $this->assertSame('Qiang', $speaker['firstName']);
        $this->assertTrue(isset($speaker['firstName']));

        // iteration
        $attributes = [];
        foreach ($speaker as $key => $attribute) {
            $attributes[$key] = $attribute;
        }
        $this->assertSame([
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
        $this->assertSame([], $singer->rules());
        $this->assertSame([], $singer->attributeLabels());
    }

    public function testDefaultScenarios()
    {
        $singer = new Singer();
        $this->assertSame(['default' => ['lastName', 'underscore_style', 'test']], $singer->scenarios());

        $scenarios = [
            'default' => ['id', 'name', 'description'],
            'administration' => ['name', 'description', 'is_disabled'],
        ];
        $model = new ComplexModel1();
        $this->assertSame($scenarios, $model->scenarios());
        $scenarios = [
            'default' => ['id', 'name', 'description'],
            'suddenlyUnexpectedScenario' => ['name', 'description'],
            'administration' => ['id', 'name', 'description', 'is_disabled'],
        ];
        $model = new ComplexModel2();
        $this->assertSame($scenarios, $model->scenarios());
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
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Invalid validation rule: a rule must specify both attribute names and validator type.');

        $invalid = new InvalidRulesModel();
        $invalid->createValidators();
    }

    /**
     * Ensure 'safe' validator works for write-only properties.
     * Normal validator can not work here though.
     */
    public function testValidateWriteOnly()
    {
        $model = new WriteOnlyModel();

        $model->setAttributes(['password' => 'test'], true);
        $this->assertSame('test', $model->passwordHash);

        $this->assertTrue($model->validate());
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

class WriteOnlyModel extends Model
{
    public $passwordHash;

    public function rules()
    {
        return [
            [['password'], 'safe'],
        ];
    }

    public function setPassword($pw)
    {
        $this->passwordHash = $pw;
    }
}
