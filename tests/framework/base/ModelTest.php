<?php

namespace yiiunit\framework\base;

use Yii;
use yii\base\Model;
use yiiunit\data\base\RulesModel;
use yiiunit\TestCase;
use yiiunit\data\base\PersonModel;
use yiiunit\data\base\Speaker;
use yiiunit\data\base\Singer;
use yiiunit\data\base\InvalidRulesModel;

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
        $this->setExpectedException('yii\base\InvalidConfigException', 'Invalid validation rule: a rule must specify both attribute names and validator type.');

        $invalid = new InvalidRulesModel();
        $invalid->createValidators();
    }
    
    public function testCatalogues()
    {
        $this->assertEquals(
            [
                'F' => Yii::t('yii', 'Femenine'),
                'M' => Yii::t('yii', 'Masculine'),
            ],
            PersonModel::getCatalogue('gender_index')
        );
        
        $this->assertEquals(
            [
                'black' =>  '#000000',
                'blue' =>  '#0000FF',
                'green' => '#00FF00',
            ],
            PersonModel::getCatalogue('eyecolor_index')
        );
        
        $this->assertNull(PersonModel::getCatalogue('unexistant'));
        
        $this->assertEquals('#000000', PersonModel::getTerminology('eyecolor_index', 'black'));
        $this->assertNull(PersonModel::getTerminology('eyecolor_index', 'orange'));

        $person = new PersonModel();
        
        $person->gender_index = 'F';
        $person->eyecolor_index = 'black';
        $person->height = 145;
        
        $this->assertTrue($person->validate());
        $this->assertEquals(Yii::t('yii', 'Femenino'), $person->gender);
        $this->assertEquals('#000000', $person->eyecolor);
        $this->assertEquals(Yii::t('yii', 'Petite'), $person->bodyType);
        
        $person->gender_index = 'X';
        $person->eyecolor_index = 'orange';
        
        $this->assertFalse($person->validate());
        $this->assertNull($person->gender);
        $this->assertNull($person->eyecolor);
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
