<?php
/**
 * ActionFilter class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\util\ArrayHelper;

/**
 * ActionFilter is the base class for all action filters.
 *
 * A filter can be applied to a controller action at different stages of its life cycle. In particular,
 * it responds to the following events that are raised when an action is being executed:
 *
 * 1. authorize
 * 2. beforeAction
 * 3. beforeRender
 * 4. afterRender
 * 5. afterAction
 *
 * Derived classes may respond to these events by overriding the corresponding methods in this class.
 * For example, to create an access control filter, one may override the [[authorize()]] method.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionFilter extends Behavior
{
	/**
	 * @var Controller the owner of this behavior. For action filters, this should be a controller object.
	 */
	public $owner;
	/**
	 * @var array IDs (case-insensitive) of actions that this filter applies to.
	 * If this property is empty or not set, it means this filter applies to all actions.
	 * Note that if an action appears in [[except]], the filter will not apply to this action, even
	 * if the action also appears in [[only]].
	 * @see exception
	 */
	public $only;
	/**
	 * @var array IDs (case-insensitive) of actions that this filter does NOT apply to.
	 */
	public $except;

	public function init()
	{
		$this->owner->on('authorize', array($this, 'handleEvent'));
		$this->owner->on('beforeAction', array($this, 'handleEvent'));
		$this->owner->on('beforeRender', array($this, 'handleEvent'));
		$this->owner->getEventHandlers('afterRender')->insertAt(0, array($this, 'handleEvent'));
		$this->owner->getEventHandlers('afterAction')->insertAt(0, array($this, 'handleEvent'));
	}

	public function authorize($event)
	{
	}

	public function beforeAction($event)
	{
	}

	public function beforeRender($event)
	{
	}

	public function afterRender($event)
	{
	}

	public function afterAction($event)
	{
	}

	public function handleEvent($event)
	{
		if ($this->applyTo($event->action)) {
			$this->{$event->name}($event);
		}
	}

	public function applyTo(Action $action)
	{
		return (empty($this->only) || ArrayHelper::search($action->id, $this->only, false) !== false)
			&& (empty($this->except) || ArrayHelper::search($action->id, $this->except, false) === false);
	}
}