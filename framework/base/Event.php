<?php

namespace yii\base;

/**
 * Event is the base class for all event classes.
 *
 * It encapsulates the parameters associated with an event.
 * The {@link sender} property describes who raises the event.
 * And the {@link handled} property indicates if the event is handled.
 * If an event handler sets {@link handled} to true, those handlers
 * that are not invoked yet will not be invoked anymore.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CComponent.php 3001 2011-02-24 16:42:44Z alexander.makarow $
 * @package system.base
 * @since 1.0
 */
class Event extends Component
{
	/**
	 * @var object the sender of this event
	 */
	public $sender;
	/**
	 * @var boolean whether the event is handled. Defaults to false.
	 * When a handler sets this true, the rest of the uninvoked event handlers will not be invoked anymore.
	 */
	public $handled=false;

	/**
	 * Constructor.
	 * @param mixed $sender sender of the event
	 */
	public function __construct($sender=null)
	{
		$this->sender=$sender;
	}
}
