<?php
/**
 * RenderEvent class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * RenderEvent represents the event parameter used for when calling [[Controller::render()]].
 *
 * By setting the [[isValid]] property, one may control whether to continue the rendering.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class RenderEvent extends Event
{
	/**
	 * @var Action the action currently being executed
	 */
	public $action;
	/**
	 * @var boolean whether the action is in valid state and its life cycle should proceed.
	 */
	public $isValid = true;

	/**
	 * Constructor.
	 * @param Action $action the action associated with this action event.
	 */
	public function __construct(Action $action)
	{
		$this->action = $action;
	}
}
