<?php
/**
 * Event class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Event is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The [[sender]] property describes who raises the event.
 * And the [[handled]] property indicates if the event is handled.
 * If an event handler sets [[handled]] to be true, the rest of the
 * uninvoked handlers will no longer be called to handle the event.
 * Additionally, an event may specify extra parameters via the [[data]] property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Event extends \yii\base\Object
{
	/**
	 * @var string the event name. This property is set by [[Component::trigger()]].
	 * Event handlers may use this property to check what event it is handling.
	 */
	public $name;
	/**
	 * @var object the sender of this event
	 */
	public $sender;
	/**
	 * @var boolean whether the event is handled. Defaults to false.
	 * When a handler sets this to be true, the event processing will stop and
	 * ignore the rest of the uninvoked event handlers.
	 */
	public $handled = false;
	/**
	 * @var mixed extra data associated with the event.
	 */
	public $data;

	/**
	 * Constructor.
	 *
	 * @param mixed $sender sender of the event
	 * @param mixed $data extra data associated with the event
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($sender = null, $data = null, $config = array())
	{
		$this->sender = $sender;
		$this->data = $data;
		parent::__construct($config);
	}
}
