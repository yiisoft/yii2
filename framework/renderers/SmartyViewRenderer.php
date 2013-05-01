<?php
/**
 * Smarty view renderer class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\renderers;

use Yii;
use Smarty;
use yii\base\View;
use yii\base\ViewRenderer;
use yii\helpers\Html;

/**
 * SmartyViewRenderer allows you to use Smarty templates in views.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class SmartyViewRenderer extends ViewRenderer
{
	/**
	 * @var string the directory or path alias pointing to where Smarty code is located.
	 */
	public $smartyPath = '@app/vendors/Smarty';

	/**
	 * @var string the directory or path alias pointing to where Smarty cache will be stored.
	 */
	public $cachePath = '@app/runtime/Smarty/cache';

	/**
	 * @var string the directory or path alias pointing to where Smarty compiled templates will be stored.
	 */
	public $compilePath = '@app/runtime/Smarty/compile';

	/**
	 * @var Smarty
	 */
	public $smarty;

	public function init()
	{
		require_once(Yii::getAlias($this->smartyPath) . '/Smarty.class.php');
		$this->smarty = new Smarty();
		$this->smarty->setCompileDir(Yii::getAlias($this->compilePath));
		$this->smarty->setCacheDir(Yii::getAlias($this->cachePath));

		$this->smarty->registerPlugin('function', 'path', array($this, 'smarty_function_path'));
	}

	/**
	 * Smarty template function to get a path for using in links
	 *
	 * Usage is the following:
	 *
	 * {path route='blog/view' alias=$post.alias user=$user.id}
	 *
	 * where route is Yii route and the rest of parameters are passed as is.
	 *
	 * @param $params
	 * @param \Smarty_Internal_Template $template
	 *
	 * @return string
	 */
	public function smarty_function_path($params, \Smarty_Internal_Template $template)
	{
		if(!isset($params['route'])) {
			trigger_error("path: missing 'route' parameter");
		}

		array_unshift($params, $params['route']) ;
		unset($params['route']);

		return Html::url($params);
	}

	/**
	 * Renders a view file.
	 *
	 * This method is invoked by [[View]] whenever it tries to render a view.
	 * Child classes must implement this method to render the given view file.
	 *
	 * @param View $view the view object used for rendering the file.
	 * @param string $file the view file.
	 * @param array $params the parameters to be passed to the view file.
	 *
	 * @return string the rendering result
	 */
	public function render($view, $file, $params)
	{
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		/** @var \Smarty_Internal_Template $template */
		$template = $this->smarty->createTemplate($file, null, null, $params, true);

		$template->assign('app', \Yii::$app);
		$template->assign('this', $view);

		return $template->fetch();
	}
}