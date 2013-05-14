<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Object;


class ActiveCollection extends Object implements \ArrayAccess
{
	/**
	 * @var ActiveRecord[]
	 */
	private $_models;

	/**
	 * @param array ActiveRecord[] $models
	 */
	public function __construct($models = array())
	{
		$this->_models = $models;
	}

	/**
	 * @param array $attributes list of attributes that should be validated.
	 * If this parameter is empty, it means any attribute listed in the applicable
	 * validation rules should be validated.
	 * @param boolean $clearErrors whether to call [[clearErrors()]] before performing validation
	 * @return boolean whether the validation is successful without any error for each model in collection.
	 */
	public function validate($attributes = null, $clearErrors = true)
	{
		$valid = true;
		foreach($this->_models as $model) {
			$valid = $model->validate($attributes, $clearErrors) && $valid;
		}
		return $valid;
	}

	/**
	 * @param boolean $runValidation whether to perform validation before saving the record.
	 * If the validation fails, the record will not be saved to database.
	 * @param array $attributes list of attributes that need to be saved. Defaults to null,
	 * meaning all attributes that are loaded from DB will be saved.
	 * @return boolean whether the saving succeeds for each model on collection.
	 */
	public function save($runValidation = true, $attributes = null)
	{
		$success = true;
		foreach($this->_models as $model) {
			$success = $model->save($runValidation, $attributes) && $result;
		}
		return $success;

	}

	/**
	 * Populates models collection from the given data array.
	 * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array.
	 * @return boolean whether at least one model from collection is successfully populated with the data.
	 */
	public function populate($data)
	{
		$success = false;
		foreach ($this->_models as $n => $model) {
			if (isset($data[$n])) {
				// @todo we need move Controller::populate() to model
				$model->populate($data[$n]);
				$success = true;
			}
		}
		return $success;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($models[$offset])`.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->_models[$offset]);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$model = $models[$offset];`.
	 * @param mixed $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->_models[$offset];
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$models[$offset] = $model;`.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->_models[$offset] = $item;
	}

	/**
	 * Sets the element value at the specified offset to null.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($models[$offset])`.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->_models[$offset]);
	}
}
