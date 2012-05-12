<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use yii\base\Action;
use yii\base\Exception;

/**
 * Controller is the base class of console command classes.
 *
 * A controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yiic` program is used when calling a console command, like the following:
 *
 * ~~~
 * yiic <route> [...options...]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * This method is invoked when extra parameters are provided to an action when it is executed.
	 * The default implementation does nothing.
	 * @param Action $action the action being executed
	 * @param array $expected the expected action parameters (name => value)
	 * @param array $actual the actual action parameters (name => value)
	 * @throws Exception if any unrecognized parameters are provided
	 */
	public function extraActionParams($action, $expected, $actual)
	{
		unset($expected['args'], $actual['args']);

		$keys = array_diff(array_keys($actual), array_keys($expected));
		if (!empty($keys)) {
			throw new Exception(\Yii::t('yii', 'Unknown parameters: {params}', array(
				'{params}' => implode(', ', $keys),
			)));
		}
	}
}