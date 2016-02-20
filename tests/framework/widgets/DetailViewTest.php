<?php
/**
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 */
namespace yiiunit\framework\widgets;
use yii\base\Arrayable;
use yii\base\ArrayableTrait;
use yii\base\DynamicModel;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/**
 * @group widgets
 */
class DetailViewTest extends \yiiunit\TestCase
{
	/** @var DetailView */
	public $detailView;

	protected function setUp()
	{
		parent::setUp();

		$this->mockWebApplication();
	}

	public function testArrayableModel()
	{
		$expectedValue = [
			[
				'attribute' => 'id',
				'format' => 'text',
				'label' => 'Id',
				'value' => 1
			],
			[
				'attribute' => 'text',
				'format' => 'text',
				'label' => 'Text',
				'value' => 'I`m arrayable'
			],
		];

		$model = new ArrayableMock();
		$model->id = 1;
		$model->text = 'I`m arrayable';

		$this->detailView = new DetailView([
			'model' => $model,
		]);

		$this->assertEquals($expectedValue, $this->detailView->attributes);
	}

	public function testObjectModel()
	{
		$expectedValue = [
			[
				'attribute' => 'id',
				'format' => 'text',
				'label' => 'Id',
				'value' => 1
			],
			[
				'attribute' => 'text',
				'format' => 'text',
				'label' => 'Text',
				'value' => 'I`m an object'
			],
		];

		$model = new ObjectMock();
		$model->id = 1;
		$model->text = 'I`m an object';

		$this->detailView = new DetailView([
			'model' => $model,
		]);

		$this->assertEquals($expectedValue, $this->detailView->attributes);
	}

	public function testArrayModel()
	{
		$expectedValue = [
			[
				'attribute' => 'id',
				'format' => 'text',
				'label' => 'Id',
				'value' => 1
			],
			[
				'attribute' => 'text',
				'format' => 'text',
				'label' => 'Text',
				'value' => 'I`m an array'
			],
		];

		$model = [
			'id' => 1,
			'text' => 'I`m an array'
		];

		$this->detailView = new DetailView([
			'model' => $model,
		]);

		$this->assertEquals($expectedValue, $this->detailView->attributes);
	}
}

/**
 * Helper Class
 */
class ArrayableMock implements Arrayable
{
	use ArrayableTrait;

	public $id;

	public $text;
}

/**
 * Helper Class
 */
class ObjectMock
{
	public $id;

	public $text;
}