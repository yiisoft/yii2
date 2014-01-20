<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\ArrayAccessTrait;
use yii\base\InvalidConfigException;

/**
 * BaseActiveFixture is the base class for fixture classes that support accessing fixture data as ActiveRecord objects.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class BaseActiveFixture extends DbFixture implements \IteratorAggregate, \ArrayAccess, \Countable
{
	use ArrayAccessTrait;

	/**
	 * @var string the AR model class associated with this fixture.
	 * @see tableName
	 */
	public $modelClass;
	/**
	 * @var array the data rows. Each array element represents one row of data (column name => column value).
	 */
	public $data = [];
	/**
	 * @var \yii\db\ActiveRecord[] the loaded AR models
	 */
	private $_models = [];


	/**
	 * Returns the AR model by the specified model name.
	 * A model name is the key of the corresponding data row in [[data]].
	 * @param string $name the model name.
	 * @return null|\yii\db\ActiveRecord the AR model, or null if the model cannot be found in the database
	 * @throws \yii\base\InvalidConfigException if [[modelClass]] is not set.
	 */
	public function getModel($name)
	{
		if (!isset($this->data[$name])) {
			return null;
		}
		if (array_key_exists($name, $this->_models)) {
			return $this->_models[$name];
		}

		if ($this->modelClass === null) {
			throw new InvalidConfigException('The "modelClass" property must be set.');
		}
		$row = $this->data[$name];
		/** @var \yii\db\ActiveRecord $modelClass */
		$modelClass = $this->modelClass;
		/** @var \yii\db\ActiveRecord $model */
		$model = new $modelClass;
		$keys = [];
		foreach ($model->primaryKey() as $key) {
			$keys[$key] = isset($row[$key]) ? $row[$key] : null;
		}
		return $this->_models[$name] = $modelClass::find($keys);
	}
}
