<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * CreateAction implements the API endpoint for creating a new model from the given data.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class CreateAction extends Action
{
	/**
	 * @var string the scenario to be assigned to the new model before it is validated and saved.
	 */
	public $scenario = Model::SCENARIO_DEFAULT;
	/**
	 * @var boolean whether to start a DB transaction when saving the model.
	 */
	public $transactional = true;
	/**
	 * @var string the name of the view action. This property is need to create the URL when the mode is successfully created.
	 */
	public $viewAction = 'view';


	/**
	 * Creates a new model.
	 * @return \yii\db\ActiveRecordInterface the model newly created
	 * @throws \Exception if there is any error when creating the model
	 */
	public function run()
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this);
		}

		/**
		 * @var \yii\db\ActiveRecord $model
		 */
		$model = new $this->modelClass([
			'scenario' => $this->scenario,
		]);

		$model->load(Yii::$app->getRequest()->getBodyParams(), '');

		if ($this->transactional && $model instanceof ActiveRecord) {
			if ($model->validate()) {
				$transaction = $model->getDb()->beginTransaction();
				try {
					$model->insert(false);
					$transaction->commit();
				} catch (\Exception $e) {
					$transaction->rollback();
					throw $e;
				}
			}
		} else {
			$model->save();
		}

		if (!$model->hasErrors()) {
			$response = Yii::$app->getResponse();
			$response->setStatusCode(201);
			$id = implode(',', array_values($model->getPrimaryKey(true)));
			$response->getHeaders()->set('Location', $this->controller->createAbsoluteUrl([$this->viewAction, 'id' => $id]));
		}

		return $model;
	}
}
