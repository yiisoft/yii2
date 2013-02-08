<?php
/**
 * UrlManager class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Object;

/**
 * UrlManager manages the URLs of Yii applications.
 * array(
 *     'pattern' => 'post/<id:\d+>',
 *     'route' => 'post/view',
 *     'params' => array('id' => 1),
 * )
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends Object
{
	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;
	/**
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 */
	public $params = array();
	/**
	 * @var string the route to the controller action
	 */
	public $route;

	public function createUrl($route, $params, $ampersand)
	{

	}

	public function parse($path)
	{
		$route = '';
		$params = array();
		return array($route, $params);
	}
}
