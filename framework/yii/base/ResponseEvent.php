<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ResponseEvent represents the event data for the [[Application::EVENT_RESPONSE]] event.
 *
 * Event handlers can modify the content in [[response]] or replace [[response]]
 * with a new response object. The updated or new response will
 * be used as the final out of the application.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ResponseEvent extends Event
{
	/**
	 * @var Response the response object associated with this event.
	 * You may modify the content in this response or replace it
	 * with a new response object. The updated or new response will
	 * be used as the final out.
	 */
	public $response;

	/**
	 * Constructor.
	 * @param Response $response the response object associated with this event.
	 * @param array $config the configuration array for initializing the newly created object.
	 */
	public function __construct($response, $config = array())
	{
		$this->response = $response;
		parent::__construct($config);
	}
}
