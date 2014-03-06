<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\rest;

use Yii;

/**
 * OptionsAction responds to the OPTIONS request by sending back an `Allow` header.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class OptionsAction extends \yii\base\Action
{
	/**
	 * @var array the HTTP verbs that are supported by the collection URL
	 */
	public $collectionOptions = ['GET', 'POST', 'HEAD', 'OPTIONS'];
	/**
	 * @var array the HTTP verbs that are supported by the resource URL
	 */
	public $resourceOptions = ['GET', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];


	/**
	 * Responds to the OPTIONS request.
	 * @param string $id
	 */
	public function run($id = null)
	{
		if (Yii::$app->getRequest()->getMethod() !== 'OPTIONS') {
			Yii::$app->getResponse()->setStatusCode(405);
		}
		$options = $id === null ? $this->collectionOptions : $this->resourceOptions;
		Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $options));
	}
}
