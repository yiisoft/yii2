<?php
/**
 * Controller class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Action;
use yii\base\Exception;
use yii\base\HttpException;

/**
 * Controller is the base class of Web controllers.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	private $_pageTitle;

	/**
	 * Returns the request parameters that will be used for action parameter binding.
	 * Default implementation simply returns an empty array.
	 * Child classes may override this method to customize the parameters to be provided
	 * for action parameter binding (e.g. `$_GET`).
	 * @return array the request parameters (name-value pairs) to be used for action parameter binding
	 */
	public function getActionParams()
	{
		return $_GET;
	}

	/**
	 * This method is invoked when the request parameters do not satisfy the requirement of the specified action.
	 * The default implementation will throw an exception.
	 * @param Action $action the action being executed
	 * @param Exception $exception the exception about the invalid parameters
	 * @throws HttpException $exception a 400 HTTP exception
	 */
	public function invalidActionParams($action, $exception)
	{
		throw new HttpException(400, \Yii::t('yii', 'Your request is invalid.'));
	}

	/**
	 * @return string the page title. Defaults to the controller name and the action name.
	 */
	public function getPageTitle()
	{
		if($this->_pageTitle !== null) {
			return $this->_pageTitle;
		}
		else {
			$name = ucfirst(basename($this->id));
			if($this->action!==null && strcasecmp($this->action->id,$this->defaultAction))
				return $this->_pageTitle=\Yii::$application->name.' - '.ucfirst($this->action->id).' '.$name;
			else
				return $this->_pageTitle=\Yii::$application->name.' - '.$name;
		}
	}

	/**
	 * @param string $value the page title.
	 */
	public function setPageTitle($value)
	{
		$this->_pageTitle = $value;
	}
}