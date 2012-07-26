<?php
/**
 * ApplicationComponent class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ApplicationComponent is the base class for application component classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ApplicationComponent extends Component
{
	/**
	 * @var string unique ID of this application component
	 */
	public $id;

	public function init()
	{
		parent::init();
		if ($this->id === null) {
			$this->id = get_class($this);
		}
	}
}
