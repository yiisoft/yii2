<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Response extends Component
{
	/**
	 * @event ResponseEvent an event that is triggered by [[send()]] before it sends the response to client.
	 * You may respond to this event to modify the response before it is sent out.
	 */
	const EVENT_SEND = 'send';

	/**
	 * @var integer the exit status. Exit statuses should be in the range 0 to 254.
	 * The status 0 means the program terminates successfully.
	 */
	public $exitStatus = 0;

	/**
	 * Sends the response to client.
	 * This method will trigger the [[EVENT_SEND]] event. Please make sure you call
	 * the parent implementation first if you override this method.
	 */
	public function send()
	{
		$this->trigger(self::EVENT_SEND, new ResponseEvent($this));
	}
}
