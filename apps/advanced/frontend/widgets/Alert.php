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
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcerative.ru>
 */
class Alert extends \yii\bootstrap\Widget
{
	/**
	 * @var array the alert types configuration for the flash messages.
	 * This array is setup as $key => $value, where:
	 * - $key is the name of the session flash variable
	 * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
	 */
	public $alertTypes = [
		'error'   => 'danger', 
		'danger'  => 'danger', 
		'success' => 'success', 
		'info'    => 'info', 
		'warning' => 'warning'
	];
	 
	/**
	 * @var array the options for rendering the close button tag.
	 */
	public $closeButtonOptions = [];
	
	public function init()
	{
		parent::init();

		$session = \Yii::$app->getSession();
		$flashes = $session->getAllFlashes();
		$appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
		
		foreach ($flashes as $type => $message) {
			/* initialize css class for each alert box in loop */
			$this->options['class'] = 'alert-' . $this->alertTypes[$type] . $appendCss; 

			/* assign unique id to each alert box in the loop */
			$this->options['id'] = $this->getId() . '-' . $type;

			echo \yii\bootstrap\Alert::widget([
				'body' => $message,
				'closeButton' => $this->closeButtonOptions,
				'options' => $this->options
			]);

			$session->removeFlash($type);
		}
	}
}
