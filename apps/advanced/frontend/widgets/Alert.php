<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\widgets;

use yii\helpers\Html;

/**
 * Alert widget renders a message from session flash. You can set message as following:
 *
 * - \Yii::$app->getSession()->setFlash('error', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('success', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('info', 'This is the message');
 *
 * @author Alexander Makarov <sam@rmcerative.ru>
 */
class Alert extends \yii\bootstrap\Alert
{
	private $_doNotRender = false;
	public function init()
	{
		if ($this->body = \Yii::$app->getSession()->getFlash('error')) {
			Html::addCssClass($this->options, 'alert-error');
		} elseif ($this->body = \Yii::$app->getSession()->getFlash('success')) {
			Html::addCssClass($this->options, 'alert-success');
		} elseif ($this->body = \Yii::$app->getSession()->getFlash('info')) {
			Html::addCssClass($this->options, 'alert-info');
		} elseif ($this->body = \Yii::$app->getSession()->getFlash('warning')) {

		} else {
			$this->_doNotRender = true;
			return;
		}

		parent::init();
	}

	public function run()
	{
		if (!$this->_doNotRender) {
			parent::run();
		}
	}
}
