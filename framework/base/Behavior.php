<?php
/**
 * Behavior class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Behavior is the base class for all behavior classes.
 *
 * A behavior can be used to enhance the functionality of an existing component.
 * In particular, it can "inject" its own properties and events into the component
 * and make them directly accessible via the component.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Behavior extends Component
{
	private $_owner;

	/**
	 * Declares event handlers for the [[owner]]'s events.
	 *
	 * Child classes may override this method to declare which methods in this behavior
	 * should be attached to which events of the [[owner]] component.
	 * The methods will be attached to the [[owner]]'s events when the behavior is
	 * attached to the owner; and they will be detached from the events when
	 * the behavior is detached from the component.
	 *
	 * The method should return an array whose keys are the names of the owner's events
	 * and values are the names of the behavior methods. For example,
	 *
	 * ~~~
	 * array(
	 *     'onBeforeValidate' => 'myBeforeValidate',
	 *     'onAfterValidate' => 'myAfterValidate',
	 * )
	 * ~~~
	 *
	 * @return array events (keys) and the corresponding behavior method names (values).
	 */
	public function events()
	{
		return array();
	}

	/**
	 * Attaches the behavior object to the component.
	 * The default implementation will set the [[owner]] property
	 * and attach event handlers as declared in [[events]].
	 * Make sure you call the parent implementation if you override this method.
	 * @param Component $owner the component that this behavior is to be attached to.
	 */
	public function attach($owner)
	{
		$this->_owner = $owner;
		foreach($this->events() as $event=>$handler) {
			$owner->attachEventHandler($event, array($this, $handler));
		}
	}

	/**
	 * Detaches the behavior object from the component.
	 * The default implementation will unset the [[owner]] property
	 * and detach event handlers declared in [[events]].
	 * Make sure you call the parent implementation if you override this method.
	 * @param Component $owner the component that this behavior is to be detached from.
	 */
	public function detach($owner)
	{
		foreach($this->events() as $event=>$handler) {
			$owner->detachEventHandler($event, array($this, $handler));
		}
		$this->_owner = null;
	}

	/**
	 * Returns the owner component that this behavior is attached to.
	 * @return Component the owner component that this behavior is attached to.
	 */
	public function getOwner()
	{
		return $this->_owner;
	}
}
