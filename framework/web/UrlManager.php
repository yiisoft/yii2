<?php
/**
 * UrlManager class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use \yii\base\Component;

/**
 * UrlManager manages the URLs of Yii applications.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlManager extends Component
{
	public $routeVar = 'r';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		$this->processRules();
	}

	/**
	 * Processes the URL rules.
	 */
	protected function processRules()
	{
	}

	/**
	 * Parses the user request.
	 * @param HttpRequest $request the request application component
	 * @return string the route (controllerID/actionID) and perhaps GET parameters in path format.
	 */
	public function parseUrl($request)
	{
		if(isset($_GET[$this->routeVar]))
			return $_GET[$this->routeVar];
		else
			return '';
	}
}
