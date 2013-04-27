<?php
/**
 * Smarty view renderer class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\renderers;

use \yii\base\View;
use \yii\base\ViewRenderer;

/**
 * SmartyViewRenderer allows you to use Smarty templates in views.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class SmartyViewRenderer extends ViewRenderer
{
	/**
	 * @var string alias pointing to where Smarty code is located.
	 */
	public $smartyDir = '@app/vendors/Smarty';

	/**
	 * @var string alias pointing to where Smarty cache will be stored.
	 */
	public $cacheDir = '@app/runtime/Smarty/cache';

	/**
	 * @var string alias pointing to where Smarty compiled teamplates will be stored.
	 */
	public $compileDir = '@app/runtime/Smarty/compile';

	/**
	 * @var string file extension to use for template files
	 */
	public $fileExtension = 'tpl';

	/** @var \Smarty */
	protected $_smarty;

	public function init()
	{
		require_once(\Yii::getAlias($this->smartyDir).'/Smarty.class.php');
		$this->_smarty = new \Smarty();
		$this->_smarty->setCompileDir(\Yii::getAlias($this->compileDir));
		$this->_smarty->setCacheDir(\Yii::getAlias($this->cacheDir));
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
		if($ext === $this->fileExtension) {
			/** @var \Smarty_Internal_Template $template */
			$template = $this->_smarty->createTemplate($file, null, null, $params, true);
			return $template->fetch();
		}
		else {
			return $view->renderPhpFile($file, $params);
		}
	}
}