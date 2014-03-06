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
 * UpdateAction implements the API endpoint for updating a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UpdateAction extends Action
{
	/**
	 * @var string the scenario to be assigned to the model before it is validated and updated.
	 */
	public $scenario = Model::SCENARIO_DEFAULT;
	/**
	 * @var boolean whether to start a DB transaction when saving the model.
	 */
	public $transactional = true;


	/**
	 * Updates an existing model.
	 * @param string $id the primary key of the model.
	 * @return \yii\db\ActiveRecordInterface the model being updated
	 * @throws \Exception if there is any error when updating the model
	 */
	public function run($id)
	{
		/** @var ActiveRecord $model */
		$model = $this->findModel($id);

		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}

		$model->scenario = $this->scenario;
		$model->load(Yii::$app->getRequest()->getBodyParams(), '');

		if ($this->transactional && $model instanceof ActiveRecord) {
			if ($model->validate()) {
				$transaction = $model->getDb()->beginTransaction();
				try {
					$model->update(false);
					$transaction->commit();
				} catch (\Exception $e) {
					$transaction->rollback();
					throw $e;
				}
			}
		} else {
			$model->save();
		}

		return $model;
	}
}
