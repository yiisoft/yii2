<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\console;

use Yii;
use yii\base\Action;
use yii\base\InvalidRequestException;
use yii\base\InvalidRouteException;

/**
 * Controller is the base class of console command classes.
 *
 * A controller consists of one or several actions known as sub-commands.
 * Users call a console command by specifying the corresponding route which identifies a controller action.
 * The `yiic` program is used when calling a console command, like the following:
 *
 * ~~~
 * yiic <route> [--param1=value1 --param2 ...]
 * ~~~
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * @var boolean whether the call of [[confirm()]] requires a user input.
	 * If false, [[confirm()]] will always return true no matter what user enters or not.
	 */
	public $interactive = true;

	/**
	 * Runs an action with the specified action ID and parameters.
	 * If the action ID is empty, the method will use [[defaultAction]].
	 * @param string $id the ID of the action to be executed.
	 * @param array $params the parameters (name-value pairs) to be passed to the action.
	 * @return integer the status of the action execution. 0 means normal, other values mean abnormal.
	 * @throws InvalidRouteException if the requested action ID cannot be resolved into an action successfully.
	 * @see createAction
	 */
	public function runAction($id, $params = array())
	{
		if ($params !== array()) {
			$class = new \ReflectionClass($this);
			foreach ($params as $name => $value) {
				if ($class->hasProperty($name)) {
					$property = $class->getProperty($name);
					if ($property->isPublic() && !$property->isStatic() && $property->getDeclaringClass()->getName() === get_class($this)) {
						$this->$name = $value;
						unset($params[$name]);
					}
				}
			}
		}
		return parent::runAction($id, $params);
	}

	/**
	 * Validates the parameter being bound to actions.
	 * This method is invoked when parameters are being bound to the currently requested action.
	 * Child classes may override this method to throw exceptions when there are missing and/or unknown parameters.
	 * @param Action $action the currently requested action
	 * @param array $missingParams the names of the missing parameters
	 * @param array $unknownParams the unknown parameters (name=>value)
	 * @throws InvalidRequestException if there are missing or unknown parameters
	 */
	public function validateActionParams($action, $missingParams, $unknownParams)
	{
		if (!empty($missingParams)) {
			throw new InvalidRequestException(Yii::t('yii', 'Missing required options: {params}', array(
				'{params}' => implode(', ', $missingParams),
			)));
		} elseif (!empty($unknownParams)) {
			throw new InvalidRequestException(Yii::t('yii', 'Unknown options: {params}', array(
				'{params}' => implode(', ', $unknownParams),
			)));
		}
	}

	/**
	 * Asks user to confirm by typing y or n.
	 *
	 * @param string $message to echo out before waiting for user input
	 * @param boolean $default this value is returned if no selection is made.
	 * @return boolean whether user confirmed
	 */
	public function confirm($message, $default = false)
	{
		if ($this->interactive) {
			echo $message . ' (yes|no) [' . ($default ? 'yes' : 'no') . ']:';
			$input = trim(fgets(STDIN));
			return empty($input) ? $default : !strncasecmp($input, 'y', 1);
		} else {
			return true;
		}
	}

	public function error($message)
	{
		echo "\nError: $message\n";
		Yii::$application->end(1);
	}

	public function globalOptions()
	{
		return array();
	}
}