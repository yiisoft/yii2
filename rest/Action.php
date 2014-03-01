<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

/**
 * Action is the base class for action classes that implement RESTful API.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends \yii\base\Action
{
	/**
	 * @var string class name of the model which will be handled by this action.
	 * The model class must implement [[ActiveRecordInterface]].
	 * This property must be set.
	 */
	public $modelClass;
	/**
	 * @var callable a PHP callable that will be called to return the model corresponding
	 * to the specified primary key value. If not set, [[findModel()]] will be used instead.
	 * The signature of the callable should be:
	 *
	 * ```php
	 * function ($id, $action) {
	 *     // $id is the primary key value. If composite primary key, the key values
	 *     // will be separated by comma.
	 *     // $action is the action object currently running
	 * }
	 * ```
	 *
	 * The callable should return the model found, or throw an exception if not found.
	 */
	public $findModel;
	/**
	 * @var callable a PHP callable that will be called when running an action to determine
	 * if the current user has the permission to execute the action. If not set, the access
	 * check will not be performed. The signature of the callable should be as follows,
	 *
	 * ```php
	 * function ($action, $model = null) {
	 *     // $model is the requested model instance.
	 *     // If null, it means no specific model (e.g. IndexAction)
	 * }
	 * ```
	 */
	public $checkAccess;


	/**
	 * @inheritdoc
	 */
	public function init()
	{
		if ($this->modelClass === null) {
			throw new InvalidConfigException(get_class($this) . '::$modelClass must be set.');
		}
	}

	/**
	 * Returns the data model based on the primary key given.
	 * If the data model is not found, a 404 HTTP exception will be raised.
	 * @param string $id the ID of the model to be loaded. If the model has a composite primary key,
	 * the ID must be a string of the primary key values separated by commas.
	 * The order of the primary key values should follow that returned by the `primaryKey()` method
	 * of the model.
	 * @return ActiveRecordInterface the model found
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function findModel($id)
	{
		if ($this->findModel !== null) {
			return call_user_func($this->findModel, $id, $this);
		}

		/**
		 * @var ActiveRecordInterface $modelClass
		 */
		$modelClass = $this->modelClass;
		$keys = $modelClass::primaryKey();
		if (count($keys) > 1) {
			$values = explode(',', $id);
			if (count($keys) === count($values)) {
				$model = $modelClass::find(array_combine($keys, $values));
			}
		} else {
			$model = $modelClass::find($id);
		}

		if (isset($model)) {
			return $model;
		} else {
			throw new NotFoundHttpException("Object not found: $id");
		}
	}
}
