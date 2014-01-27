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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
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

	public function testDynamicProperty()
	{
		$email = 'invalid';
		$name = 'long name';
		$model = new DynamicModel(compact('name', 'email'));
		$this->assertEquals($email, $model->email);
		$this->assertEquals($name, $model->name);
		$this->setExpectedException('yii\base\UnknownPropertyException');
		$age = $model->age;
	}
}
