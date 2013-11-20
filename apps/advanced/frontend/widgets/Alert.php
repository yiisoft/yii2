<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace frontend\widgets;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * - \Yii::$app->getSession()->setFlash('error', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('success', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('info', 'This is the message');
 *
 * @author Alexander Makarov <sam@rmcerative.ru>
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 */
class Alert extends \yii\bootstrap\Widget
{
	/**
	 * @var array the allowed bootstrap alert types.
	 */
	public $allowedTypes = ['error', 'danger', 'success', 'info', 'warning'];
	 
	/**
	 * @var array the options for rendering the close button tag.
	 */
	public $closeButton = [];
	
	public function init()
	{
		$session = \Yii::$app->getSession();
		$flashes = $session->getAllFlashes();
		$appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

		foreach ($flashes as $type => $message) {
			if (in_array($type, $this->allowedTypes)) {
				$this->options['class'] = (($type === 'error') ? 'alert-danger' : 'alert-' . $type) . $appendCss;
				echo \yii\bootstrap\Alert::widget([
					'body' => $message,
					'closeButton' => $this->closeButton,
					'options' => $this->options
				]);
				$session->removeFlash($type);
			}
		}
		parent::init();
	}
}
