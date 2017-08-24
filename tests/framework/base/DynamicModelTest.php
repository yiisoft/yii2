<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\base;

use yii\base\DynamicModel;
use yiiunit\TestCase;

/**
 * @group base
 */
class DynamicModelTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testValidateData()
    {
        $email = 'invalid';
        $name = 'long name';
        $age = '';
        $model = DynamicModel::validateData(compact('name', 'email', 'age'), [
            [['email', 'name', 'age'], 'required'],
            ['email', 'email'],
            ['name', 'string', 'max' => 3],
        ]);
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('email'));
        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('age'));
    }

    public function testAddRule()
    {
        $model = new DynamicModel();
        $this->assertSame(0, $model->getValidators()->count());
        $model->addRule('name', 'string', ['min' => 12]);
        $this->assertSame(1, $model->getValidators()->count());
        $model->addRule('email', 'email');
        $this->assertSame(2, $model->getValidators()->count());
        $model->addRule(['name', 'email'], 'required');
        $this->assertSame(3, $model->getValidators()->count());
    }

    public function testValidateWithAddRule()
    {
        $email = 'invalid';
        $name = 'long name';
        $age = '';
        $model = new DynamicModel(compact('name', 'email', 'age'));
        $model->addRule(['email', 'name', 'age'], 'required')
            ->addRule('email', 'email')
            ->addRule('name', 'string', ['max' => 3])
            ->validate();
        $this->assertTrue($model->hasErrors());
        $this->assertTrue($model->hasErrors('email'));
        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('age'));
    }

    public function testDynamicProperty()
    {
        $email = 'invalid';
        $name = 'long name';
        $model = new DynamicModel(compact('name', 'email'));
        $this->assertSame($email, $model->email);
        $this->assertSame($name, $model->name);
        $this->expectException('yii\base\UnknownPropertyException');
        $age = $model->age;
    }

    public function testLoad()
    {
        $dynamic = new DynamicModel();
        //define two attributes
        $dynamic->defineAttribute('name');
        $dynamic->defineAttribute('mobile');
        // define rule
        $dynamic->addRule(['name', 'mobile'], 'required');
        // define your sample data
        $data = [
            'DynamicModel' => [
                'name' => $name = 'your name 2',
                'mobile' => $mobile = 'my number mobile',
            ],
        ];
        // load data
        $this->assertFalse($dynamic->load([]));
        $this->assertTrue($dynamic->load($data));

        $this->assertTrue($dynamic->validate());
        $this->assertSame($name, $dynamic->name);
        $this->assertSame($mobile, $dynamic->mobile);
    }
}
