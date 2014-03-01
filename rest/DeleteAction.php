<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;
use yii\db\ActiveRecord;

/**
 * DeleteAction implements the API endpoint for deleting a model.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DeleteAction extends Action
{
	/**
	 * @var boolean whether to start a DB transaction when deleting the model.
	 */
	public $transactional = true;


	/**
	 * Deletes a model.
	 */
	public function run($id)
	{
		$model = $this->findModel($id);

		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this, $model);
		}

		if ($this->transactional && $model instanceof ActiveRecord) {
			$transaction = $model->getDb()->beginTransaction();
			try {
				$model->delete();
				$transaction->commit();
			} catch (\Exception $e) {
				$transaction->rollback();
				throw $e;
			}
		} else {
			$model->delete();
		}

		Yii::$app->getResponse()->setStatusCode(204);
	}
}
