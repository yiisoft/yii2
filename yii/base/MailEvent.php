<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */

class MailEvent extends Event
{

	/**
	 * @var \yii\mail\MessageInterface mail message being send
	 */
	public $message;
	/**
	 * 
	 */
	public $isSuccessful;
}
