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
use yii\base\InlineAction;
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
			$options = $this->globalOptions();
			foreach ($params as $name => $value) {
				if (in_array($name, $options, true)) {
					$this->$name = $value;
					unset($params[$name]);
				}
			}
		}
		return parent::runAction($id, $params);
	}

	/**
	 * Binds the parameters to the action.
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 * This method will first bind the parameters with the [[globalOptions()|global options]]
	 * available to the action. It then validates the given arguments.
	 * @param Action $action the action to be bound with parameters
	 * @param array $params the parameters to be bound to the action
	 * @return array the valid parameters that the action can run with.
	 * @throws Exception if there are unknown options or missing arguments
	 */
	public function bindActionParams($action, $params)
	{
		if ($params !== array()) {
			$options = $this->globalOptions();
			foreach ($params as $name => $value) {
				if (in_array($name, $options, true)) {
					$this->$name = $value;
					unset($params[$name]);
				}
			}
		}

		$args = isset($params[Request::ANONYMOUS_PARAMS]) ? $params[Request::ANONYMOUS_PARAMS] : array();
		unset($params[Request::ANONYMOUS_PARAMS]);
		if ($params !== array()) {
			throw new Exception(Yii::t('yii', 'Unknown options: {params}', array(
				'{params}' => implode(', ', array_keys($params)),
			)));
		}

		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$missing = array();
		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();
			if (!isset($args[$i])) {
				if ($param->isDefaultValueAvailable()) {
					$args[$i] = $param->getDefaultValue();
				} else {
					$missing[] = $name;
				}
			}
		}

		if ($missing !== array()) {
			throw new Exception(Yii::t('yii', 'Missing required arguments: {params}', array(
				'{params}' => implode(', ', $missing),
			)));
		}

		return $args;
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

	/**
	 * Returns the names of the global options for this command.
	 * A global option requires the existence of a global member variable whose
	 * name is the option name.
	 * Child classes may override this method to specify possible global options.
	 * @return array the names of the global options for this command.
	 */
	public function globalOptions()
	{
		return array();
	}
}